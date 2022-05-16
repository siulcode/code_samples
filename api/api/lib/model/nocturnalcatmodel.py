from mysql.connector import MySQLConnection, Error
from lib.python_mysql_dbconfig import read_db_config


class NocturnalCatModel:
    count = None

    def insert_facts(self, facts):
        query = '''
        INSERT INTO catwarrior_facts(
                cuid,date_of_birth,fact,first_name,last_name
            )
        VALUES(%s,%s,%s,%s,%s)'''
        try:
            db_config = read_db_config()
            conn = MySQLConnection(**db_config)
            cursor = conn.cursor()
            for item in facts:
                cuid = item.get('cuid')
                date_of_birth = item.get('date_of_birth')
                fact = item.get('fact')
                first_name = item.get('first_name')
                last_name = item.get('last_name')
                args = (cuid, date_of_birth, fact, first_name, last_name)
                cursor = conn.cursor()
                cursor.execute(query, args)
                if cursor.lastrowid:
                    print('last insert id', cursor.lastrowid)
                else:
                    print('last insert id not found')
                conn.commit()
        except Error as e:
            print('Error:', e)
        finally:
            cursor.close()
            conn.close()

    def fetchall(self):
        try:
            dbconfig = read_db_config()
            conn = MySQLConnection(**dbconfig)
            cursor = conn.cursor()
            cursor.execute("SELECT * FROM catwarrior_facts")
            self.rows = cursor.fetchall()
            self.count = cursor.rowcount
            return self.rows
        except Error as e:
            print(e)
        finally:
            cursor.close()
            conn.close()

    def delete_fact_entry(self, id):
        db_config = read_db_config()
        query = "DELETE FROM catwarrior_facts WHERE id = %s"
        try:
            conn = MySQLConnection(**db_config)
            cursor = conn.cursor()
            cursor.execute(query, (id,))
            conn.commit()
        except Error as error:
            print(error)
        finally:
            cursor.close()
            conn.close()
