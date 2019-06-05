# -*- coding: utf-8 -*-
from flask import jsonify, request, session
from sqlalchemy import text, exc
from config import *
import json
import hashlib


class Category:
    """ Категории (ремонт электроники, ремонт бытовой техники и т.п.) """
    def get_by_city_id(self, city_id):
        """Возвращает категории, которые есть в конкретном городе"""
        sql = text("""SELECT categories.category_id, categories.category_name, categories.description
                      FROM categories, city_categories
                      WHERE categories.category_id = city_categories.category_id
                      AND city_categories.city_id = :city_id
                      ORDER BY city_categories.category_id ASC""")
        result = execute(sql, city_id=city_id)
        categories = []
        for i in result:
            categories.append([i.category_id, i.category_name, i.description])
        return categories

    def amount_by(self, city_id):
        sql = text("""SELECT COUNT(category_id) FROM city_categories WHERE city_id = :city_id""")
        result = execute(sql, city_id=city_id).fetchone()
        return result[0]

class City:
    """ Используется для получения информации о городах (id, имя, общее количество...) """

    def get_all(self):
        """Возвращает id городов и имён"""
        sql = text("""SELECT city_id, city_name FROM cities ORDER BY city_id""")
        result = execute(sql)
        cities = []
        for i in result:
            cities.append([i.city_id, i.city_name])
        return cities

    def get_id_by_email(self, email):
        sql = text("""SELECT city_id FROM customers WHERE email = :email""")
        try:
            result = execute(sql, email=email).fetchone()
            return result.city_id
        except:
            return

class Customer:

    def add_new_email(self, email=None):
        if email and not self.is_email_exist(email):
            sql = text("""INSERT INTO customers (email) VALUES (:email)""")
            try:
                execute(sql, email=email)
                return
            except:
                return
        else:
            return

    def add_new_phone(self, phone=None):
        if phone and not self.is_phone_exist(phone):
            sql = text("""INSERT INTO customers (phone) VALUES (:phone)""")
            try:
                execute(sql, phone=phone)
                return
            except:
                return
        else:
            return

    def authorize(self, email, password):

        print('authorize: ', email, password)

        sql = text("""SELECT password FROM customers WHERE email = :email""")

        if not email or not password:
            print('not email or not password')
            return False

        try:
            result = execute(sql, email=email).fetchone()
            password_hash = hashlib.sha256(password.encode('utf8')).hexdigest()
            print(result, password_hash)
        except:
            return user.create_message("error", USRMSG_ERROR_OCCURED)

        result = True if result.password == password_hash else False

        if result:
            city_id = city.get_id_by_email(email)
            customer_session.write(city_id, None, email, None, 'customer', True)
            return user.create_message("success", "Вход в личный кабигнет...")
        else:
            return user.create_message('error', 'Неверный логин или пароль')

    def get_id_by_email(self, email):

        sql = text("""SELECT customer_id FROM customers WHERE email = :email""")

        try:
            result = execute(sql, email=email).fetchone()
            return result[0]
        except:
            return

    def get_id_by_phone(self, phone):
        sql = text("""SELECT customer_id FROM customers WHERE phone = :phone""")
        try:
            result = execute(sql, phone=phone).fetchone()
            return result[0]
        except:
            return

    def is_email_exist(self, email=None):
        sql = text("""SELECT 1 FROM customers WHERE email = :email""")
        try:
            result = execute(sql, email=email).fetchone()
        except:
            return
        return True if result else False

    def is_phone_exist(self, phone=None):
        sql = text("""SELECT 1 FROM customers WHERE phone = :phone""")
        try:
            result = execute(sql, phone=phone).fetchone()
        except:
            return
        return True if result else False

    def is_fully_registered(self, email=None, phone=None):
        if email and self.is_email_exist(email):
            sql = text("""SELECT 1 FROM customers WHERE email = :email AND password IS NOT NULL""")
        elif phone and self.is_phone_exist(phone):
            sql = text("""SELECT 1 FROM customers WHERE phone = :phone AND password IS NOT NULL""")
        try:
            result = execute(sql, email=email, phone=phone).fetchone()
        except:
            return
        return True if result else False

    def register_new(self, city_id, email, password):
        sql = text("""INSERT INTO customers (email, password, city_id) VALUES (:email, :password, :city_id)""")
        if email and not self.is_email_exist(email):
            try:
                execute(sql, email=email, password=hashlib.sha256(password.encode('utf8')).hexdigest(), city_id=city_id)
                customer_session.write(city_id, None, email, None, 'customer', False)
                return user.create_message('sucess', 'Новый пользователь успешно создан! Переход в личный кабинет...')
            except:
                return user.create_message("error", USRMSG_ERROR_OCCURED)
        elif self.get_id_by_email(email):
            return user.create_message('info', 'Вы уже отправляли быстрый запрос используя этот email, нажмите <СЮДА>, чтобы создать пароль для Вашего аккаунта. Таким образом Вы будете полноценно зарегистрированы')
        else:
            return user.create_message('warning', 'Пользователь с этим email уже существует!')

class CustomerRequest:
    def add(self, category_id, request_type_id, subcategory_id, title_text, request_text):
        sql = text("""INSERT INTO requests (category_id, request_type_id, subcategory_id, customer_id, title_text, request_text)
                      VALUES (:category_id, :request_type_id, :subcategory_id, :customer_id, :title_text, :request_text)""")
        try:
            execute(sql, category_id=category_id, request_type_id=request_type_id, subcategory_id=subcategory_id,
                    customer_id=session['user_id'], title_text=title_text, request_text=request_text)
            return user.create_message("success", "Ваш запрос успешно отправлен!")
        except:
            return user.create_message("error", USRMSG_ERROR_OCCURED)

class CustomerSession:
    def initialize(self):
        if not 'user_type' in session:
            session['city_id'] = None
            session['category_id'] = None # нужно или нет?
            session['email'] = None # нужно или нет?
            session['phone'] = None # нужно или нет?
            session['user_type'] = 'guest'
            session['authorized'] = False
            session['user_id'] = None
        return

    def write(self,
                city_id=None,
                category_id=None,
                email=None,
                phone=None,
                user_type=None,
                authorized=False):

        session['city_id'] = city_id
        session['category_id'] = category_id
        session['email'] = email
        session['phone'] = phone
        session['user_type'] = user_type
        session['authorized'] = authorized
        session['user_id'] = customer.get_id_by_email(email) if email else customer.get_id_by_phone(phone)
        print('user phone is: ', phone, " AND user_id is: ", session['user_id'])
        return

class Contractor:

    def is_email_exist(self, email=None):
        sql = text("""SELECT 1 FROM contractors WHERE email = :email""")
        try:
            result = execute(sql, email=email).fetchone()
        except:
            return
        return True if result else False

    def is_phone_exist(self, phone=None):
        sql = text("""SELECT 1 FROM contractors
                        WHERE phone_1 = :phone
                        OR phone_2 = :phone
                        OR phone_3 = :phone
                        OR phone_4 = :phone
                        OR phone_5 = :phone""")
        try:
            result = execute(sql, phone=phone).fetchone()
        except:
            return
        return True if result else False

class RequestType:
    """ Типы запросов """
    def get_by_category_id(self, category_id):
        """ Получить типы запросов (ремонт, настройка и т.п.), относящиеся к id категории """
        sql = text("""SELECT request_type_id, request_type_name FROM request_types WHERE category_id = :category_id ORDER BY request_type_id""")
        try:
            result = execute(sql, category_id=category_id)
        except:
            return
        request_types = []
        for i in result:
            request_types.append([i.request_type_id, i.request_type_name])
        return request_types

class Subcategory:
    """ Подкатегории (сотовые телефоны, планшеты, компьютеры и т.д.) """
    def get_by_category_id(self, category_id):
        """ Получить подкатегории (сотовые, планшеты, компьютеры и т.п.), относящиеся к id категории """
        sql = text("""SELECT subcategory_id, subcategory_name FROM subcategories WHERE category_id = :category_id ORDER BY subcategory_id""")
        try:
            result = execute(sql, category_id=category_id)
        except:
            return
        subcategories = []
        for i in result:
            subcategories.append([i.subcategory_id, i.subcategory_name])
        return subcategories

class User:
    """ Общий класс, подразумевающий и Customers и Contractors """

    def get_current(self):
        return jsonify(user_email=session['email'],
                       user_type=session['user_type'])

    def create_message(self, msg_type, msg, url=''):
        """ Подготовка сообщения пользователю к отправке.
        msg_type (тип сообщения) может быть:
        'success' - успешная обработка данных, т.е. всё прошло как надо
        'info' - информационные сообщения
        'warning' - предупреждающие сообщения
        'error' - неисправимая ошибка требующая перезагрузки страницы
        """
        return json.dumps({"msg_type" : msg_type, "msg" : msg, "url": url})

customer = Customer()
user = User()
customer_session = CustomerSession()
city = City()