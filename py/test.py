# -*- coding: utf-8 -*-
from app import *
from common import *
from config import *
from auth import *


session = {}
session['user_id'] = 213
session['user_type'] = 213
# print(customer.register_by_email('NUIOR@ASND.www'))
# print(is_phone_exist('1580000000'))

def authorize(email, password):

    sql = text("""SELECT password FROM customers WHERE email = :email""")

    if not email or not password:
        print('not email or not password')
        return False
    try:
        result = execute(sql, email=email).fetchone()
        password_hash = hashlib.sha256(password.encode('utf8')).hexdigest()
        print(result.password, password_hash)
    except:
        return user.create_message("error", USRMSG_ERROR_OCCURED)

    result = True if result.password == password_hash else False
    print(result)

    if result:
        city_id = city.get_id_by_email(email)
        # customer_session.write(city_id, None, email, None, 'customer', False)
        return user.create_message("success", "Вход в личный кабигнет...")

    else:
        return user.create_message('error', 'Неверный логин или пароль')

print(authorize('fefefe10@ggg.com', '123456'))
