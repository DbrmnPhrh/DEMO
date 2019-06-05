# -*- coding: utf-8 -*-
from flask import Flask, render_template, request, session, url_for
import json
from config import *
from common import Category, City, Customer, CustomerRequest, CustomerSession, RequestType, Subcategory, User


app = Flask(__name__)
app.debug = True
app.secret_key = 'hahaha' # read about Python secret_key using
app_root = '/qix/'
category = Category()
city = City()
customer = Customer()
customer_session = CustomerSession()
customer_request = CustomerRequest()
request_type = RequestType()
subcategory = Subcategory()
user = User()

@app.route(app_root, methods=['GET'])
def index():
    customer_session.initialize()
    return render_template('index.html')

@app.route(app_root+'customer/', methods=['GET'])
def customer_get_offers():
    return render_template('customer_get_offers.html')

@app.route(app_root+'users/', methods=['GET'])
def get_current_user():
    return user.get_current()

@app.route(app_root+'cities/', methods=['GET'])
def get_cities():
    return json.dumps(city.get_all())

@app.route(app_root+'categories/', methods=['GET'])
def get_categories():
    city_id = request.args.get('city_id')
    return json.dumps(category.get_by_city_id(city_id))

@app.route(app_root+'request_types/', methods=['GET'])
def get_request_types():
    category_id = request.args.get('category_id')
    return json.dumps(request_type.get_by_category_id(category_id))

@app.route(app_root+'subcategories/', methods=['GET'])
def get_subcategories():
    category_id = request.args.get('category_id')
    return json.dumps(subcategory.get_by_category_id(category_id))

@app.route(app_root+'register/', methods=['POST'])
def register():
    city_id = request.form.get('city_id')
    user_type = request.form.get('user_type')
    email = request.form.get('reg_email')
    password = request.form.get('reg_password')

    if user_type == 'customer':
        return customer.register_new(city_id, email, password)
    elif user_type == 'contractor' and not CONTRACTOR_REG_DISABLE:
        pass
    return ''

@app.route(app_root+'auth/', methods=['POST'])
def auth():
    email = request.form.get('email')
    password = request.form.get('password')
    print(email)
    print(password)
    try:
        return customer.authorize(email, password)
    except:
        return
    return

@app.route(app_root+'logout/', methods=['GET'])
def logout():
    session.clear()
    return json.dumps({"url": url_for('index')})

@app.route(app_root+'requests/', methods=['GET', 'POST'])
def add_request():
    if request.method == 'GET':
        pass
    elif request.method == 'POST':
        city_id = request.form.get('city_id')
        category_id = request.form.get('category_id')
        request_type_id = request.form.get('request_type_id')
        subcategory_id = request.form.get('subcategory_id')
        title_text = request.form.get('title_text')
        request_text = request.form.get('request_text')
        email = request.form.get('user_email')
        phone = request.form.get('user_phone')
        user_type = 'customer' if session['user_type'] == 'customer' else 'guest'

        if email:
            customer_session.write(city_id, category_id, email, None, user_type, False)
            customer.add_new_email(email)
        elif phone:
            customer_session.write(city_id, category_id, None, phone, user_type, False)
            customer.add_new_phone(phone)
        else:
            return

    return customer_request.add(category_id, request_type_id, subcategory_id, title_text, request_text)

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=8000)