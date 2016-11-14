
class NATHelper

    # a class method that returns the next number in a circular array of consecutive numbers
    def self.get_next_index(params)
        curr_index = params[:curr_index]
        confObj = params[:confObj]
        # try to implement a circular index:
        # i.e. for a 1-based array of size n, i.e. [1,2...n]
        # it would return the next index on the list, unless curr_index == n, in which
        # case it'll return 1
        return 1 if curr_index == confObj.azIds.length
        return curr_index += 1
    end

    def self.gen_route_table_list(params)
        curr_index = params[:curr_index]
        confObj = params[:confObj]
        parameters = params[:parameters]
        rt_prefix = params[:rt_prefix]
        # construct the list of the route tables as a single parameter to 
        # pass to the interpolate() call:
        # the list of route tables returned is related to the NAT instance we are creating:
        # the route table that is in the same AZ as the NAT instance will always be at the front
        # this is to indicate this is the default route table for the NAT instance to take over
        # every time
        route_tables = []
        confObj.azIds.each.with_index(1) do |azId, index|
            rt_value = parameters["#{rt_prefix}#{index}"]
            route_tables.push(rt_value)
        end
        (1 .. (curr_index-1)).each do |index|
            elem = route_tables.shift
            route_tables.push(elem)
        end
        return route_tables.join(" ")
    end

    def self.given_full_nat_info(params)
        confObj = params[:confObj]
        parameters = params[:parameters]
        res1 = confObj.azIds.each.with_index(1).select { |azId, index|
            parameters["natrt#{index}"] != "NONE"
        }
        res2 = confObj.azIds.each.with_index(1).select { |azId, index|
            parameters["natinstance#{index}"] != "NONE"
        }
        # checking that everything passed the two checks above
        (res1.length + res2.length == 2 * confObj.azIds.length)
    end


end
