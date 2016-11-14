require 'rubygems'
require 'graphviz'
require 'yaml'
require 'pp'

abort "need to fix this dependency on vpcsize.yaml when we have 2 separate files for the misc and nomisc VPC types - see http://dashboard.news.com.au/browse/AUTOMATION-584"

vpcdesign = YAML::load(File.open('../maps/vpcsize.yaml'))

subnets = vpcdesign['Mappings']['VPCSubnetMap']['small']
lookup = vpcdesign['Mappings']['VPCSubnets']

#puts lookup
subclass = []
subnets.each do |sub,size|
  networks = lookup.select{|key, hash| hash.include? sub}.keys 
  subclass <<   [ sub, networks ]
end

pp subclass

# Create a new graph
g = GraphViz.new( "structs", :type => :graph )

dc = g.add_nodes("directconnect")
vpn = g.add_nodes("vpn")
newscorp = g.add_nodes("newscorp")
internet = g.add_nodes("internet - 0.0.0.0/0")
igw = g.add_nodes("igw")
vgw = g.add_nodes("vgw")
priv_rt = g.add_nodes("privatert1")
pub_rt = g.add_nodes("publicrt1")
nata_rt = g.add_nodes("natrt1")
natb_rt = g.add_nodes("natrt2")

# Create two nodes
subclass.each do |sub,network|
  
  first = network.first
  #puts network.second
  
    if first == "Private" then
      if sub != "MISC" then
        sub = g.add_nodes(sub)
        g.add_edges( sub ,priv_rt)      
      end

    end
    if first == "Public" then
      if sub == "NAT" then
        
        g.add_nodes(sub+"-A")
        g.add_nodes(sub+"-B")
        g.add_edges( sub+"-A" , igw)
        g.add_edges( sub+"-B" , igw)
        g.add_edges( nata_rt , sub+"-A")
        g.add_edges( natb_rt , sub+"-B")
        

      end      
      
      
    end    

  
  if network[1] then
    second =  network[1]
    if second == "Misc" then
      g.add_edges( sub , nata_rt)
      g.add_edges( sub , natb_rt)

    end  
  end
end

g.add_edges(priv_rt,vgw)
g.add_edges(pub_rt,igw)
g.add_edges(vgw,dc)
g.add_edges(vgw,vpn)
g.add_edges(igw,internet)




g.add_edges(natb_rt,vgw)
g.add_edges(nata_rt,vgw)

g.add_edges(dc,newscorp)
g.add_edges(vpn,newscorp)

# Create an edge between the two nodes
#g.add_edges( hello, world )


# Generate output image
g.output( :png => "hello_world.png" )
