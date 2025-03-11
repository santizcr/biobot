import telebot
# Example (Python with python-telegram-bot library)
from telegram import InlineKeyboardButton, InlineKeyboardMarkup
from telegram.ext import Updater, CommandHandler, CallbackContext

from config import token
from telebot import types
from telebot.types import WebAppInfo
token='7773919465:AAF6QVOfrUwvgZyouzolGAoX-79JWkr9amc'
bot=telebot.TeleBot(token)
@bot.message_handler(commands=['start'])
def start_message(message):
  webbrowser.open("https://santizcr.github.io/biobot/")
bot.polling(none_stop=True, interval=0)