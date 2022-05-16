from flask import jsonify, request, Flask
from lib.catwarlord import CatWarlord
from lib.model.nocturnalcatmodel import NocturnalCatModel


api_helper = CatWarlord()
api_model = NocturnalCatModel()
app = Flask(__name__)
app.config["DEBUG"] = True

####################################
# RUNTIME STARTS HERE

# WEB ROUTES #


@app.route('/', methods=['GET'])
def home():
    facts = api_helper.filter_warlord_facts()  # Fetch new data
    api_model.insert_facts(facts)
    s = '''
    <h1>Meaw!</h1>
    <p>Endpoint to query our nifty Cat warlord API.</p>
    <p>
        View all of our filtered entries here:
        <a href="{}/api/v1/quotes/allfacts" />
            Here
        </a>
    </p>
    '''.format(api_helper.base_url)

    return s


@app.route('/api/v1/quotes/allfacts', methods=['GET'])
def quote_allfacts():
    rows = api_model.fetchall()
    print('Total Row(s):', api_model.count)
    for row in rows:
        print(row)
    return jsonify(rows)


@app.route('/api/v1/delete/<id>', methods=['DELETE'])
def delete_fact_by_id(id):
    if request.method == 'DELETE':
        api_helper.delete_fact_entry(id)
        return "Fact with ID: {} has been deleted".format(id)
    else:
        return "<p>Error 405 Method Not Allowed", 405


@app.errorhandler(404)
def page_not_found(e):
    return "<p>Meou! This cat food is yucky and can't be found!</p>", 404


# START WEB SERVER #
app.run(port=8181)
