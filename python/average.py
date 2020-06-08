
f = open('api-average.log')

api1 = {'get_foo': []}
api2 = {'get_bar': []}

# Lets clean up our data.
for line in f:
    if 'get_foo' in line:
        for key, value in api1.items():
            line = ''.join(filter(str.isdigit, line))
            value.append(line)
    elif 'get_bar' in line:
        for k, v in api2.items():
            line = ''.join(filter(str.isdigit, line))
            v.append(line)

# get_foo
num1 = []
for number in api1['get_foo']:
    num1.append(int(number))
average1 = sum(num1) / len(api1['get_foo'])

# get_bar
num2 = []
for number in api2['get_bar']:
    num2.append(int(number))
average2 = sum(num2) / len(api2['get_bar'])

data = {'get_foo': average1, 'get_bar': average2}

for k, v in data.items():
    num = str(int(v))
    num = num[6:-1]
    print("{}: average = {}".format(k, num))
