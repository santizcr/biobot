import telebot

from config import token
from telebot import types
from telebot.types import WebAppInfo
token='5734832509:AAEjGHrKGKVa0FKLEMT7E67atoOe3xLBhOA'
bot=telebot.TeleBot(token)
@bot.message_handler(commands=['start'])
def start_message(message):
  webbrowser.open("https://flanyx.github.io/")
bot.polling(none_stop=True, interval=0)