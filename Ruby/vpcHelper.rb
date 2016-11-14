require 'json'
require_relative 'vpc-table'

class VpcHelper

    def initialize()

    end

    def write_stack_outputs_to_db(params)
        action = params[:action]
        args = params[:args]
        stack_outputs = params[:stack_outputs]
        options = params[:options]
        cfn_templates = params[:cfn_templates]
        logger = params[:logger]

        stack_outputs = stack_outputs.merge(options: options)
        stack_outputs = stack_outputs.merge(cfn_templates: cfn_templates)
        if action == "CREATE" then
            stack_outputs = stack_outputs.merge(create_cmd_line_opts: args)
            logger.debug 'Writing to Dynamo' + stack_outputs.to_json
            VPCDB.put(JSON.parse(stack_outputs.to_json))
        elsif action == "UPDATE" then
            stack_outputs = stack_outputs.merge(update_cmd_line_opts: args)
            VPCDB.put(JSON.parse(stack_outputs.to_json))
        end
    end


    def create_s3_endpoint(params)
        ec2 = params[:ec2]
        stack_outputs = params[:stack_outputs]
        stack_name = params[:stack_name]
        region = params[:region]
        vpcType = params[:vpcType]
        confObj = params[:confObj]
        logger = params[:logger]

        requestor_id = stack_outputs['vpcid']
        route_table_ids = []
        if vpcType == "nomisc" then
            confObj.azIds.each.with_index(1) do |azId, index|
                route_table_ids << stack_outputs["privatert#{index}"]
            end
        else
            route_table_ids = [stack_outputs['privatert1']]
        end
        serviceName = "com.amazonaws." + region + ".s3"
        endpoint_object = ec2.describe_vpc_endpoints(filters: [{name: "service-name", values: [serviceName]},{name: "vpc-id", values: [requestor_id]}])
        logger.debug endpoint_object
        if endpoint_object['vpc_endpoints'].length >= 1 then
            logger.warn "(#{stack_name} s3 endpoint exists"
        else
            logger.info "(#{stack_name} Creating VPC endpoint for #{serviceName}"
            ec2.create_vpc_endpoint(
                dry_run: false,
                vpc_id: requestor_id,
                service_name: serviceName,
                route_table_ids: route_table_ids
            )
        end
    end


    def create_shared_peering_connecton(params)
        ec2 = params[:ec2]
        stack_outputs = params[:stack_outputs]
        stack_name = params[:stack_name]
        fullvpcname = params[:fullvpcname]
        logger = params[:logger]

        requestor_id = stack_outputs['vpcid']

        require_relative './yamlFactory'
        account = YAMLFactory.getAccountObj(fullvpcname)
        accepter_vpcs = account['Mappings']['Accounts'].keys

        pcx_objs = []
        accepter_vpcs.each do |vpc|

            vpc_id = account['Mappings']['Accounts'][vpc]['vpc_id']
            account_id = account['Mappings']['Accounts'][vpc]['acct_id']

            logger.warn "checking peering exists for #{vpc} #{requestor_id} (#{vpc_id}) in account: #{account_id}.."
            peer_object = ec2.describe_vpc_peering_connections(filters: [{name: "requester-vpc-info.vpc-id", values: [requestor_id]},
                                                                         {name: "accepter-vpc-info.vpc-id", values: [vpc_id] },{name: "status-code", values:["active","pending-acceptance"]} ] )['vpc_peering_connections']
            if peer_object.length == 1 then
                pcx_obj = peer_object.map(&:vpc_peering_connection_id)[0]
                logger.warn "(#{stack_name}) peering exists between #{requestor_id} & #{vpc_id} (#{pcx_obj}).. skipping"
                pcx_objs << { "id" => pcx_obj, "name" => vpc }

            else
                logger.info "(#{stack_name}) creating new peer request between #{requestor_id} and #{vpc_id}.."
                peering = ec2.create_vpc_peering_connection(
                    dry_run: false,
                    vpc_id: requestor_id,
                    peer_vpc_id: vpc_id,
                    peer_owner_id: account_id.to_s,
                )
                logger.info "Peering connection request ID: " + peering.vpc_peering_connection.vpc_peering_connection_id.inspect

                # AUTOMATION-620 - adding a delay before polling as we have run into cases where no
                # peering request was not returned, causing this function to fail
                # even though the request was indeed created
                sleep 5
                # quick and dirty poll loop
                # we need to poll the status of this request, as it always returns 'initiating-request'
                # from the create_vpc_peering_connection call, and won't become 'failed' right away
                # if indeed it's meant to fail at all
                while true do
                    pcx_objects = ec2.describe_vpc_peering_connections(
                        filters: [
                            {name: "requester-vpc-info.vpc-id", values: [requestor_id]},
                            {name: "accepter-vpc-info.vpc-id", values: [vpc_id] }
                        ] )['vpc_peering_connections']
                    if pcx_objects.length >= 1
                        pcx_object = pcx_objects[0] # just take the first one
                        if pcx_object.status.code == "failed" 
                            err_msg = "Peering failure caught, " + pcx_objects.status.message +
                                ", we should exit now! " +
                                "Another check is to make sure both your VPCs are in the same region, as otherwise it is not allowed by AWS." 
                            logger.fatal err_msg
                            raise err_msg
                        elsif ["active","pending-acceptance"].include? pcx_object.status.code
                            logger.info "The peering connection is in either the active or pending-acceptance state, that is good; as it means it has passed AWS' validation"
                            break
                        end
                    else
                        err_msg = "We shouldn't reach here, as it means our create_vpc_peering_connection call has not resulted in any peering connection requests at all!"
                        logger.fatal err_msg
                        raise err_msg
                    end
                    sleep 2
                end

                ## APPEND OUTPUT to stack_outputs so next stack can access the variables..
            end

        end
        logger.debug "all peering ids: " + pcx_objs.uniq.to_s

    end

end

