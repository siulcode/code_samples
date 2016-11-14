require 'aws-sdk'
require 'json'
require "logger"

$logger = Logger.new(STDOUT)
$logger.datetime_format = "%Y-%m-%d %H:%M:%S"


class S3Helper
    @s3_client
    @configObj

    def initialize(params)
        profile_name = 'base-account-user'
        credentials_path = File.expand_path('~/.aws/credentials')

        begin
          provider = Aws::SharedCredentials.new path: credentials_path,
            profile_name: profile_name
        rescue Aws::Errors::NoSuchProfileError
          err_msg = "lib/s3Helper.rb: Could not load profile: " + profile_name
          $logger.fatal err_msg
          abort err_msg
        end
        #$logger.debug "lib/s3Helper.rb: Loaded credentials : " + provider.loadable?.to_s

        # force the using of the instance role
        @configObj = params[:configObj]

#        # check for a provider or keys, and use them instead of the instance profile
#        # (of the Bamboo agent)
#        if !params[:provider].nil?
#            credentials = provider
#        elsif !params[:access_key_id].nil? && !params[:secret_access_key].nil?
#            credentials = Aws::Credentials.new(params[:access_key_id], params[:secret_access_key])
#        else
#            credentials = Aws::InstanceProfileCredentials.new
#        end

        @s3_client = Aws::S3::Client.new(
            region: @configObj.masterRegion,
            credentials: provider
        )

    end

    # a class method to return the S3 URL based on the region given
    # defaults to us-east-1
    # http://docs.aws.amazon.com/general/latest/gr/rande.html#s3_region
    def self.get_s3_endpoint_url(region = 'us-east-1')
        return "s3.amazonaws.com" if region == 'us-east-1'
        return "s3-#{region}.amazonaws.com"
    end

    def valid_json?(text)
        begin
            JSON.parse(text)
            return true
        rescue JSON::ParserError
            return false
        end
    end

    def gen_s3_key(params)
        if params[:s3FolderName].nil? then
            abort "gen_s3_key: s3FolderName not passed in as a parameter"
        else
            s3FolderName = params[:s3FolderName]
        end
        if params[:cfnTemplateName].nil? then
            abort "gen_s3_key: cfnTemplateName not passed in as a parameter"
        else
            cfnTemplateName = params[:cfnTemplateName]
        end
        @configObj.masterS3Folder + '/' + params[:s3FolderName] + '/' + params[:cfnTemplateName] + '.json'
    end

    def gen_s3_url(params)
        if params[:s3FolderName].nil? then
            abort "gen_s3_url: s3FolderName not passed in as a parameter"
        else
            s3FolderName = params[:s3FolderName]
        end
        if params[:cfnTemplateName].nil? then
            abort "gen_s3_url: cfnTemplateName not passed in as a parameter"
        else
            cfnTemplateName = params[:cfnTemplateName]
        end
        "http://" + @configObj.masterS3Bucket + ".s3.amazonaws.com/" +
            gen_s3_key({:s3FolderName => s3FolderName, :cfnTemplateName => cfnTemplateName})
    end

    # upload the template to S3, and return the URL to the file
    def upload_template(params)
        if params[:s3FolderName].nil? then
            abort "upload_template: s3FolderName not passed in as a parameter"
        else
            s3FolderName = params[:s3FolderName]
        end
        if params[:cfnTemplateName].nil? then
            abort "upload_template: cfnTemplateName not passed in as a parameter"
        else
            cfnTemplateName = params[:cfnTemplateName]
        end
        if params[:cfnTemplateBody].nil? then
            abort "upload_template: cfnTemplateBody not passed in as a parameter"
        else
            cfnTemplateBody = params[:cfnTemplateBody]
        end
        #puts " both parameters are good"
        if valid_json?(cfnTemplateBody)
            bucket = @configObj.masterS3Bucket
            key = gen_s3_key({:s3FolderName => s3FolderName, :cfnTemplateName => cfnTemplateName})
            puts "uploading the CFN template into the #{bucket} bucket as #{key}"

            resp = @s3_client.put_object(
                bucket: bucket,
                key: key,
                body: cfnTemplateBody
            )
            # TODO check the response
            return gen_s3_url({:s3FolderName => s3FolderName, :cfnTemplateName => cfnTemplateName})
        else
            abort "The cfnTemplateBody is not valid JSON"
        end
    end

end
