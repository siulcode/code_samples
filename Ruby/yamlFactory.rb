require 'yaml'

class YAMLFactory

   def self.getAccountObj(fullvpcname="")
       yaml_file = File.join(__dir__, "../maps/accounts.yaml")
       unless fullvpcname.empty? then
           yaml_file = File.join(__dir__, "../maps/#{fullvpcname}-accounts.yaml")
           unless File.exist? yaml_file then
               abort "YAMLFactory::getAccountObj: #{yaml_file} does not exist"
           end
       end
       account = YAML::load_file(yaml_file)
   end

end
