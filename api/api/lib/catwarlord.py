import requests
import string
from datetime import datetime, timedelta
from configparser import ConfigParser


class CatWarlord:

    base_url = None
    beamed_api = None

    def __init__(self):
        config = ConfigParser()
        config.read('config.ini')
        self.base_url = config.get('api', 'base_url')
        self.beamed_api = config.get('api', 'beamed_api')

    def fetch_feline_facts(self):
        resp = requests.get(self.beamed_api)
        if resp.status_code == 200:
            return resp.json()

    def filter_warlord_facts(self):
        fact_list = []
        name_prefix = []
        data = self.fetch_feline_facts()
        alphabet = string.ascii_uppercase
        for index, letter in enumerate(alphabet):
            if index % 2 == 0:
                name_prefix.append(letter)
        for item in data:
            str_dob = item.get('date_of_birth')
            first_name = item.get('first_name')
            obj_dob = datetime.strptime(str_dob, '%Y-%m-%d')
            allowed_dob = datetime.now() - timedelta(days=3650)
            if allowed_dob < obj_dob and first_name[0] in name_prefix:
                fact_list.append(item)
        if fact_list is not None:
            return fact_list
        else:
            raise ValueError('Nothing found based specified criteria')
