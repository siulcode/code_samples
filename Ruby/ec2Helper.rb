require 'aws-sdk'
require 'json'
require "logger"

$logger = Logger.new(STDOUT)
$logger.datetime_format = "%Y-%m-%d %H:%M:%S"

class EC2Helper
    @ec2_client
    @confObj

    def initialize(params)
        profile_name = 'new-vpc-creator'
        credentials_path = File.expand_path('~/.aws/credentials')

        begin
          provider = Aws::SharedCredentials.new path: credentials_path,
            profile_name: profile_name
        rescue Aws::Errors::NoSuchProfileError
          err_msg = "lib/ec2Helper.rb: Could not load profile: " + profile_name
          $logger.fatal err_msg
          abort err_msg
        end
        #$logger.debug "lib/ec2Helper.rb: Loaded credentials : " + provider.loadable?.to_s

        # force the using of the instance role
        @confObj = params[:confObj]

        @ec2_client = Aws::EC2::Client.new(
            region: params[:region],
            credentials: provider
        )

    end

    def get_ami_id()

      resp = @ec2_client.describe_images({
        dry_run: false,
        owners: ["amazon"],
        filters: [
          {
            name: "virtualization-type",
            values: [ "hvm" ]
          },
          {
            name: "name",
            values: [ @confObj.amiName ]
          }
        ]


      })
      resp.images[0].image_id
    end
end
