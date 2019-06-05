# -*- coding: utf-8 -*-
from flask import session as session
from sqlalchemy import text
from config import execute


# def session_write(city_id=None,
#             category_id=None,
#             email=None,
#             phone=None,
#             user_type=None,
#             authorized=None):

#     session['city_id']     = city_id
#     session['category_id'] = category_id
#     session['email']  = email
#     session['phone']  = phone
#     session['user_type']   = user_type
#     session['authorized']  = authorized

#     if user_email and user_type == 'guest':
#         session['user_id'] = get_customer_id_by_email(email=user_email)
#     elif user_phone and user_type == 'guest':
#         session['user_id'] = get_customer_id_by_phone(phone=user_phone)
#     else:
#         pass
#         # session['user_id'] = getCurrentUserId()

#     return

def registration(city_id, user_type, email, password):
    pass