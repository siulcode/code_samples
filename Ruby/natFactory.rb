
class NATFactory

    # a class method that returns the correct script name
    def self.getNATScriptName(params)
        if params[:vpcType].nil? then
            vpcType = "nomisc" # default to a nomisc VPC
        else
            vpcType = params[:vpcType]
        end

        filename = "scripts/nat-#{vpcType}.rb"

        unless File.exist? filename then
            abort "NATFactory::getNATScriptName: #{filename} does not exist!"
        end
        return filename
    end

end
