# amoHookEnd

Скрипт синхронизации карточек сделок и контактов
	
	при успешном завершении сделки отправляем hook на скрипт;
	информация из карточки сделки уходит в карточку контакта за сделкой*

		* подсчет суммы бюджета по успешным сделкам
		* подсчет среднего чека по успешным сделкам
		* подсчет количества успешных сделок
		* информация по посещенным мероприятиям отмечается в мультисписке у контакта
		* формат участия в последнем мероприятии


Логика работы:

		спрашиваем прилетевшую сделку смотрим     -> | смотрим мероприятие и формат участия 
		спрашиваем главные контакт сделки         -> | смотрим массив посещенных мероприятий                               

			спрашиваем все сделки у контакта  -> | считаем успешно завершенные
							     | суммируем бюджет у этих сделок     
							     | считаем средний чек              

			обновляем контакт:

				- посещенные мероприятия
				- сумма бюджета посещенных мероприятий
				- средний чек
				- количество посещенных мероприятий
				- последний формат участия

