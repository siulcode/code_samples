require "bundler/setup"
require "aws-sdk"
require "pp"
require "logger"
require "json"
require_relative '../config/vpcCreatorConfig'

$logger = Logger.new(STDOUT)
$logger.datetime_format = "%Y-%m-%d %H:%M:%S"

class VPCDB
    @confObj

    def self.initialize
        profile_name = 'base-account-user'
        credentials_path = File.expand_path('~/.aws/credentials')

        @confObj = VpcCreatorConfig.new
        begin
          provider = Aws::SharedCredentials.new path: credentials_path,
            profile_name: profile_name
        rescue Aws::Errors::NoSuchProfileError
          err_msg = "lib/vpc-table.rb: Could not load profile: " + profile_name
          $logger.fatal err_msg
          abort err_msg
        end
        $logger.debug "lib/vpc-table.rb: Loaded credentials : " + provider.loadable?.to_s

        Aws.config[:credentials] = provider
        Aws.config[:region] = @confObj.masterRegion
        @dbc = Aws::DynamoDB::Client.new
        @db_table = @confObj.masterVpcInfoTable 
    end  

    def self.scan
        self.initialize
        vpcs = @dbc.scan({:table_name => @db_table })
        return vpcs.data.items
    end

    def self.get(vpc_name)
        self.initialize
        vpcs = self.scan
        vpcs.each do |vpc|
            if vpc['fullvpcname'] == vpc_name then
                return vpc
            end
        end
        return nil
    end

    def self.put(item)
        self.initialize
        @dbc.put_item(:table_name => @db_table, :item => item)
    end

    def self.update(item)
        self.initialize
        @dbc.update_item(:table_name => @db_table, :item => item)
    end

end

