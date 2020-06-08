require 'yaml'
require 'json'
require 'aws-sdk'
require 'erubis'
require 'netaddr'
require 'ostruct'
require 'erb'
require "iform/ktp_secrets"

module KtpLoader

  class Delivery
    extend KtpLoader

    def _find(hash, string)
      hash.select { |key, value| key.include? string }
    end

    def fetch_stack_config
      stack_config_file = KtpLoader::Config::KTP_STACK_PROPERTIES
      config = YAML.load_file stack_config_file
    end

    def load_stack_params
      stack_config_file = KtpLoader::Config::KTP_STACK_PROPERTIES
      config            = YAML.load_file stack_config_file
      aws_resources     = [
        'asg',
        'autoscaling_policy',
        'bag',
        'cloudwatch_alarm',
        'delivery',
        'ecr_repos',
        'efs_file_system',
        'efs_mount_target',
        'eks_cluster',
        'iam_role',
        'iam_users',
        'instance_profile',
        'launch_config',
        'loadbalancer',
        'loadbalancer_v2',
        'loadbalancer_v2_listener',
        'loadbalancer_v2_target_group',
        'recorder',
        'route53',
        's3_bucket',
        'scheduled_actions',
        'securitygroups',
        'vpc_networks',
        'vpc_endpoints'
      ]

      aws_resources.each do |_resource|
        config.each do |type, resources|
          if resources.is_a? Hash
            if type.include? "#{_resource}_environment"
              # TODO: Test if resource environment is set.
              @resource_type = type
              data           = resources[config['short_env_name']]
              @resource_data = KtpLoader::Config::KTP_ENV_CONFIG_PATH + '.cache' + File::SEPARATOR + "app_data_#{_resource}.yaml"
              File.open(@resource_data,'w') { |tempfile| tempfile.write data.to_yaml }
            end
          end
        end
        source_resource = _find(config, "#{_resource}_environment")
        if !source_resource.empty?
          aws_resources                 = config.tap {|resource_needle| resource_needle.delete @resource_type}
          resource_props                = YAML.load_file @resource_data
          aws_resources[@resource_type] = resource_props
          stack_data                    = KtpLoader::Config::KTP_STACK_PROPERTIES
          File.open(stack_data,'w') { |tempfile| tempfile.write aws_resources.to_yaml}
        end
      end
    end
  end

  class Get_param

    def initialize
      @userdata = Delivery.new.load_stack_params
    end
  end

  # Class to do interpolation on UserData scripts.
  class SfEruby < Erubis::Eruby
    extend KtpLoader
      include Erubis::ArrayEnhancer

    def fetch_userdata_vars
      stack_config_file = KtpLoader::Config::KTP_STACK_PROPERTIES
      config            = YAML.load_file stack_config_file
      config.each do |type, resources|
        if resources.is_a? Hash
          resources.each do |key, value|
            if key.end_with? 'launch_config'
              if !value['userdata_data'].nil?
                data = value['userdata_data']
                app_data = KtpLoader::Config::KTP_ENV_APPDATA
                File.open(app_data,'w') { |tempfile| tempfile.write data.to_yaml }
              end
            end
          end
        end
      end

      stack_app_data    = KtpLoader::Config::KTP_ENV_APPDATA
      stack_config_file = KtpLoader::Config::KTP_STACK_PROPERTIES
      app_data          = YAML.load_file stack_app_data
      stack_properties  = YAML.load_file stack_config_file

      h_data   = app_data["#{stack_properties['short_env_name']}"]
      obj_data = JSON.parse(h_data.to_json, object_class: OpenStruct)
    end

    def add_expr(src, code, indicator)
      case indicator
        when '='
          src << " #{@bufvar} << (" << code << ');'
        else
          super
      end
    end
  end

  # Class to load CIDRs based on the VPC CIDR.
  class SubnetAddress
    extend KtpLoader

    def initialize(parent_cidr, child_mask, no_subnets_needed)
      @parent_cidr = NetAddr::CIDR.create parent_cidr
      @child_subnets = @parent_cidr.subnet(Bits: child_mask)
      if @child_subnets.length < no_subnets_needed
        abort("Subnet mask #{child_mask} is too large for the VPC with CIDR #{parent_cidr}. Can't create enough subnets to fill all selected zones.")
      end
      # Setting initial counter
      @subnet_ptr = -1
    end

    # Going through the list of subnets, one by one
    def next_subnet
      @subnet_ptr += 1
      @child_subnets[@subnet_ptr]
    end
  end

  # Class to load cross-stack dependencies.
  class Stack
    extend KtpLoader

    # Getter for stack
    attr_reader :stack

    def initialize(stackname, region, cf_client = nil)
      @stack_config_file = KtpLoader::Config::KTP_ENV_CONFIG_PATH + '.cache' + File::SEPARATOR + 'stack.yaml'
      @cf_client      = cf_client || Aws::CloudFormation::Client.new(region: region)
      @cl_resource    = Aws::CloudFormation::Resource.new client: @cf_client
      @stack          = @cl_resource.stack(stackname)
      @cached_stacks  = {}
    end

    # creates or reuses a stack by name 'name'
    def with_substack(name)
      unless @cached_stacks.key?(name)
        @cached_stacks[name] = Stack.new(physical_id(name), 'us-east-1')
      end
      @cached_stacks[name]
    end

    # returns a physical_resource_id or a stack_id depending on if the
    # logical_id is set and can be found
    def physical_id(logical_id = nil)
      if logical_id
        @stack.resource_summaries.find { |rs| rs.logical_id == logical_id }.physical_resource_id
      else
        @stack.stack_id
      end
    end

    # returns an AWS "Outputs" value if the "Key" can be found
    def get_output(output_key)
      @stack.outputs.find { |o| o.output_key == output_key }.output_value
    end

    # get all VPC subnets of a particular type (ex. all Private Subnets in the Apps VPC)
    # returns array of subnet ids
    def get_vpc_subnets_by_type(vpc, subnet_type)
      account = ENV['AWS_IAM_ACCOUNT']
      cloudops_datahub_accounts = %w(061851502621 526930246559 581833419224 239845225121)
      if cloudops_datahub_accounts.include? account
        #CLOUDOPS & DATAHUB AWS ACCOUNTS
        match = /^Subnet#{Regexp.quote(subnet_type).capitalize}Az[a-zA-Z]Vpc#{Regexp.quote(vpc).capitalize}$/
      else
        #NON-CLOUDOPS & NON-DATAHUB AWS ACCOUNTS
        match = /^#{Regexp.quote(subnet_type).capitalize}[a-zA-Z]Vpc#{Regexp.quote(vpc).capitalize}Ec2Subnet/
      end
      @stack.outputs.map { |o| o.output_value if o.output_key =~ match }.compact
    end
  end

  class Config
    include KtpSecrets

    KTP_ENV_CONFIG_PATH   = 'conf/environment/'
    KTP_STACK_PROPERTIES  = KTP_ENV_CONFIG_PATH + '.cache' + File::SEPARATOR + 'stack.yaml'
    KTP_ENV_APPDATA       = KTP_ENV_CONFIG_PATH + '.cache' + File::SEPARATOR + 'app_data.yaml'
    attr_reader :files, :get

    # @Param: <Array> Files, <String> env.
    # @Desc:
    # Accepts a string filename or an array of string filenames to parse.
    # If an array is supplied, values from later files will override values
    # of earlier files with the same name.
    # Will choke if YAML.load_file returns false (invalid or empty file)
    def initialize(files, env = 'nonprod', appenv = 'sandbox')
      validate_account_type(env)
      validate_app_env(appenv)
      create_temp_config_dir # Get our cashing dir. Using it to filter/select or ENV.
      @files      = files.respond_to?( 'map' ) ? files : [ files ]
      @get        = @files.map do |file|
        if file.end_with?('.json')
          json_file = File.read(file)
          JSON.parse json_file
        else
          if file.include? 'environment'
            @cache_dir        = KTP_ENV_CONFIG_PATH + '.cache'
            temp_config_file  = @cache_dir + File::SEPARATOR + 'env.yml'
            _file             = YAML.load_file file
            env_file          = _file[env] # Select NONPROD or PROD.
            env_file          = env_file[appenv] # Select AppEnv (Sandbox, Staging, etc...)
            File.open(temp_config_file,'w') { |tempfile| tempfile.write env_file.to_yaml }
            file = temp_config_file
          end
          KtpSecrets::Secrets.encrypt_file(file) # Let's encrypt our parameters
          YAML.load_file file
        end
      end.reduce({}, :merge!)
      store_stack(@get) # Store the stack for global use, should overwrite for each deployment.
      KtpLoader::Get_param.new # Loading all app environment values.
    end

    def store_stack(stack_data)
      stack_config_file  = @cache_dir + File::SEPARATOR + 'stack.yaml'
      File.open(stack_config_file,'w') { |tempfile| tempfile.write stack_data.to_yaml }
    end

    # @Param: <String> type.
    # @Desc:
    # Validates the environment fed by the first position on the
    # SparkleFormation Compile time parameter.
    def validate_account_type(acct_type)
      if acct_type == 'nonprod'
        puts "NONPROD parameter validated..."
      elsif acct_type == 'prod'
        puts "PROD parameter validated..."
      else
        raise "(#{acct_type.upcase}) type could not be loaded, we can lookup (nonprod, prod) only"
      end
    end

    # @Param: <String> type.
    # @Desc:
    # Validates the application environment fed by the second position on the
    # SparkleFormation Compile time parameter.
    def validate_app_env(app_env)
      if app_env == 'qa'
        puts "QA parameter validated..."
      elsif app_env == 'development'
        puts "DEVELOPMENT parameter validated..."
      elsif app_env == 'staging'
        puts "STAGING parameter validated..."
      elsif app_env == 'sandbox'
        puts "SANDBOX parameter validated..."
      elsif app_env == 'apps'
        puts "APPS parameter validated..."
      elsif app_env == 'mgmt'
        puts "MGMT parameter validated..."
      elsif app_env == 'dr'
        puts "DR parameter validated..."
      else
        raise "(#{app_env.upcase}) env could not be loaded, we can lookup (development, qa, staging, sandbox, apps, mgmt, dr) only."
      end
    end

    # @Desc:
    # Creates our caching dir if does not exist.
    def create_temp_config_dir
      begin
        Dir.mkdir(KTP_ENV_CONFIG_PATH+'.cache') unless Dir.exist?(KTP_ENV_CONFIG_PATH+'.cache')
      rescue Exception
        puts "Had an error. Make sure you are in the correct directory."
        puts "The stack directory is in <Root>/iform-stacks/stacks/"
        raise "Unable to process the iform command."
      end
    end
  end

end
