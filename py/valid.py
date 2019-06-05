# -*- coding: utf-8 -*-
from flask import session as session
from cerberus import Validator
from common import cities, categories, request_types, subcategories
from config import *

class Validate:

    def __init__(self):
        self.v = Validator()

    def integer(self, val, min, max, empty=False):
        """ Валидация целочисленных значений в диапазоне min...max.
            Пустые значения по-умолчанию недопустимы """
        schema = {'target': {'type': 'integer', 'min': min, 'max': max, 'empty': empty}}
        result = self.v.validated({'target': val}, schema)
        if not self.v.errors:
            return result['target']
        else:
            return False

    def text(self, text, min, max, empty=False):
        """ Валидация текста заданной длины.
            Пустые значения по-умолчанию недопустимы """
        schema = {'target': {'type': 'string', 'minlength': min, 'maxlength': max, 'empty': empty}}
        result = self.v.validated({'target': text}, schema)
        if not self.v.errors:
            return result['target']
        else:
            return False

    def user_type(self, user_type):
        schema = {'target': {'type': 'string', 'allowed': ['guest', 'customer', 'contractor'], 'empty': False}}
        result = self.v.validated({'target': user_type}, schema)
        if not self.v.errors:
            return result['target']
        else:
            print('write error into log...', user_type)
            return False

    def city_id(self, city_id):
        min = 1
        max = cities.amount()
        return self.integer(city_id, min, max)

    def category_id(self, category_id):
        """ category_id должна быть в диапазоне
            от 1 до количества категорий в текущем городе """
        min = 1
        max = categories.amount_by(session['city_id'])
        return self.integer(category_id, min, max)

    def request_type_id(self, request_type_id, category_id):
        min = 1
        max = request_types.amount_by(category_id)
        return self.integer(request_type_id, min, max)

    def subcategory_id(self, subcategory_id, category_id):
        min = 1
        max = subcategories.amount_by(category_id)
        return self.integer(subcategory_id, min, max)

    def title_text(self, title_text):
        title_text = str(title_text)
        return self.text(title_text, REQUEST_TITLE_MIN_LEN, REQUEST_TITLE_MAX_LEN)

    def request_text(self, request_text):
        request_text = str(request_text)
        return self.text(request_text, REQUEST_TEXT_MIN_LEN, REQUEST_TEXT_MAX_LEN)

    def user_email(self, user_email, empty=False):
        user_email = str(user_email)
        schema = {'target': {'type': 'string', 'regex': '^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$', 'empty': empty}}
        result = self.v.validated({'target': user_email}, schema)
        if not self.v.errors:
            return result['target']
        else:
            print('write error into log...', user_email)
            return False

    def user_phone(self, user_phone, empty=False):
        user_phone = str(user_phone)
        schema = {'target': {'type': 'string', 'regex': '^(?:\d{10,10}|)$', 'empty': empty}}
        result = self.v.validated({'target': user_phone}, schema)
        if not self.v.errors:
            return result['target']
        else:
            print('write error into log...', user_phone)
            return False