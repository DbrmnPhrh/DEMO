# -*- coding: utf-8 -*-
from sqlalchemy import create_engine

# константы:
REQUEST_MAX_ID = 2147483647       #DEF: 2147483647 Максимальный id запроса
REQUEST_DAYS_LIMIT = 200          #DEF: 7 Отображение запросов у contractors за указанное количество дней
REQUEST_TITLE_MIN_LEN = 5         #Минимальная длина заголовка запроса (также должно быть на клиенте)
REQUEST_TITLE_MAX_LEN = 50        #Максимальная длина заголовка запроса (также должно быть на клиенте)
REQUEST_TEXT_MIN_LEN = 10         #Минимальная длина текста запроса (также должно быть на клиенте)
REQUEST_TEXT_MAX_LEN = 600        #Максимальная длина текста запроса (также должно быть на клиенте)
OFFER_MAX_LAST_ID = 1000          #Максимальное количество последних предложений по текущему запросу
OFFER_TEXT_MAX_LEN = 600          #Максимальная длина текста предложения (также должно быть на клиенте)
OFFER_MAX_COST_FROM = 9999999     #Максимальное значение стоимости "от" (также должно быть на клиенте)
OFFER_MAX_COST_TO = 9999999       #Максимальное значение стоимости "до" (также должно быть на клиенте)
OFFER_MAX_PERIOD = 99             #Максимальное значение периода (также должно быть на клиенте)
CHAT_MSG_MIN_LEN = 10             #Минимальная длина сообщения чата
CHAT_MSG_MAX_LEN = 600            #Максимальная длина сообщения чата
CONTRACTOR_REG_DISABLE = True     #Запрет на самостоятельную регистрацию для Contractors

USRMSG_ERROR_OCCURED = "Произошла ошибка, но скоро всё будет работать"

# Конфигурация БД
engine = create_engine('mysql+mysqlconnector://akbashev.a:hwnd3264@localhost/qix_main')
execute = engine.execute