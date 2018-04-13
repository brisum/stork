<?php

namespace App\Service\Spelling;

class Month
{
	public static function genitiveCase($month)
	{
        switch ($month) {
            case 'Январь':
                return 'Января';
            case 'Февраль':
                return 'Февраля';
            case 'Март':
                return 'Марта';
            case 'Апрель':
                return 'Апреля';
            case 'Май':
                return 'Мая';
            case 'Июнь':
                return 'Июня';
            case 'Июль':
                return 'Июля';
            case 'Август':
                return 'Августа';
            case 'Сентябрь':
                return 'Сентября';
            case 'Октябрь':
                return 'Октября';
            case 'Ноябрь':
                return 'Ноября';
            case 'Декабрь':
                return 'Декабря';


            case 'Січень':
                return 'Січня';
            case 'Лютий':
                return 'Лютого';
            case 'Березень':
                return 'Березня';
            case 'Квітень':
                return 'Квітня';
            case 'Травень':
                return 'Травня';
            case 'Червень':
                return 'Червня';
            case 'Липень':
                return 'Липня';
            case 'Серпень':
                return 'Серпня';
            case 'Вересень':
                return 'Вересня';
            case 'Жовтень':
                return 'Жовтня';
            case 'Листопад':
                return 'Листопада';
            case 'Грудень':
                return 'Грудня';


            case 'January':
            case 'February':
            case 'March':
            case 'April':
            case 'May':
            case 'June':
            case 'July':
            case 'August':
            case 'September':
            case 'October':
            case 'November':
            case 'December':
                return "of {$month}";
        }

        return $month;
	}
}
