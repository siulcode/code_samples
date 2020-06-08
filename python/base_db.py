import boto3


class baseDb:

    def __init__(self):
        self.dynamodb = boto3.resource('dynamodb', region_name='us-east-1')
        self.tableName = 'adobe-tabl'

    def save_data(self, **kwargs):
        table = self.dynamodb.Table(self.tableName)
        data_row = {}
        for data in kwargs.values():
            # print('row_id', kwargs['row_id'], 'DATA TO DB: ', data)

            google_id = data['id']
            title = kwargs['title']
            data_row.update({'google_id': google_id})
            data_row.update({'title': title})

            print("Adding record:", google_id, title)
            table.put_item(Item=data_row)

    def create_adobe_table(self, dynamodb=None):
        if not self.dynamodb:
            self.dynamodb = boto3.resource('dynamodb')

        table = self.dynamodb.create_table(
            TableName=self.tableName,
            KeySchema=[
                {
                    'AttributeName': 'google_id',
                    'KeyType': 'HASH'  # Partition key
                },
                {
                    'AttributeName': 'title',
                    'KeyType': 'RANGE'  # Sort key
                }
            ],
            AttributeDefinitions=[
                {
                    'AttributeName': 'google_id',
                    'AttributeType': 'S'
                },
                {
                    'AttributeName': 'title',
                    'AttributeType': 'S'
                }

            ],
            ProvisionedThroughput={
                'ReadCapacityUnits': 10,
                'WriteCapacityUnits': 10
            }
        )
        return table
