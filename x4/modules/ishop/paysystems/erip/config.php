<?php

// --------------------------------------------------------------------------------------
// Тестовый режим приема платежей или реальный. В тестовом режиме денежные средства с покупателя не списываются
// --------------------------------------------------------------------------------------
$TEST_MODE = $this->object->test_mode;
// Е-mail для информирования о тестовых платежах
$TEST_MODE_EMAIL = $this->object->test_mode_email;

// --------------------------------------------------------------------------------------
// Данные Интернет-магазина
// --------------------------------------------------------------------------------------

// Название Интренет-магазина в платежной системе
$SHOP_NAME = $this->object->shop_name;
// Адрес сайта Интренет-магазина
$SHOP_SITE = $this->object->shop_site;
// URL для возврата клиента на сайт Интренет-магазина после оплаты заказа. Должен указывать на страницу с информацией о статусе заказа клиента, например, http://supersaller.by/order/!ORDER_NO!
$PROVIDER_URL = $this->object->provider_url;
// Е-mail Интернет-магазина
$SHOP_EMAIL = $this->object->shop_email;
// Необходимо ли отправлять сообщение об успешной оплате заказа на E-mail Интернет-магазина
$SEND_SUCC_EMAIL_TO_SHOP = $this->object->send_succ_email_to_shop;
// Необходимо ли отправлять сообщения об ошибках оплаты заказов на E-mail Интернет-магазина
$SEND_ERR_EMAIL_TO_SHOP = $this->object->send_err_email_to_shop;

// --------------------------------------------------------------------------------------
// Кол-во дней, которое действителен для оплаты новый заказ
// --------------------------------------------------------------------------------------
$ORDER_VALID_DAY_CNT = $this->object->order_valid_day_cnt;

// --------------------------------------------------------------------------------------
// Статусы заказа
// --------------------------------------------------------------------------------------

// Статус заказа после успешного списания денег с клиента в тестовом режиме
$ORDER_STATUS_TEST_MODE_PAYED = $this->object->order_status_test_mode_payed;

// Статус заказа для нового заказа, ожидающего оплату. Начальный статус для созданного клиентом заказа.
// Присваивается заказу при отмене клиентом оплаты на сайте платежной системы
// Pending - Ожидает начала оплаты
$ORDER_STATUS_CREATED = $this->object->order_status_created;


// Статус заказа после разрешения списания денег с клиента. Разрешили списать деньги с клиента. Ожидается результат оплаты.
// !!!!! Необходимо добавить в таблицу jos_vm_order_status статус W - "Списание денег" !!!!!
// Waiting for confirm - Ожидает подтверждения или отмены оплаты
$ORDER_STATUS_WAITING_FOR_PAY = $this->object->order_status_waiting_for_pay;
// Статус заказа после успешного списания денег с клиента. Заказ оплачен. Необходима доставка товара клиенту
// Confirmed - Подтвержден
$ORDER_STATUS_PAYED = $this->object->order_status_payed;
$ORDER_STATUS_CANCELED = $this->object->order_status_canceled;

// Статус заказа означающий подтверждение интернет-магазином наличия товара на складе. Может устанавливаться магазином до оплаты товара
$ORDER_STATUS_VERIFYED = $this->object->order_status_verifyed;
// Сообщение плательщику отображаемое, если наличие товара на складе еще не подтверждено магазином
// Внимание! Наличие на складе заказанных товаров будет проверено через некоторое время. Для ускорения данного процесса звоните по тел. Velcom +(375)29-???????, MTC +(375)33-???????.<br><br>Совершать оплату рекомендуем после того, как мы установим статус заказа "Подтвержден" - это будет означать, что заказанные товары точно есть в наличии.
$ORDER_NOT_VERIFYED_ATTENTION = $this->object->order_not_verifyed_attention;
// Показывать сообщение клиенту о том, что наличие товаров еще не проверено?
$ORDER_NOT_VERIFYED_ATTENTION_SHOW = $this->object->order_not_verifyed_attention_show;

// Разрешить оплачивать заказ, по которому не подтвержденно наличие товаров на складе?
$ORDER_NOT_VERIFYED_PAY_ALLOW = $this->object->order_not_verifyed_pay_allow;


// Точность платежа. Для ЕРИП в кэшинах минимальная купюра 500 руб., в кассах 100 руб.
$AMT_PRECISION = $this->object->amt_precision;

// --------------------------------------------------------------------------------------
// Константа, используемая при формировании ЦП
// --------------------------------------------------------------------------------------

// TEST
$SALT_TEST = $this->object->salt_test;
// ERIP
$SALT_ERIP = $this->object->salt_erip;
// iPayMTS
$SALT_IPAY_MTS = $this->object->salt_ipay_mts;
// iPayLIFE
$SALT_IPAY_LIFE = $this->object->salt_ipay_life;


// --------------------------------------------------------------------------------------
// Номер услуги в платежной системе
// --------------------------------------------------------------------------------------

// Тестовый режим работы
$SRV_NO_TEST = $this->object->srv_no_test;
// Реальный режим работы
$SRV_NO_ERIP = $this->object->srv_no_erip;
$SRV_NO_IPAY_MTS = $this->object->srv_no_ipay_mts;
$SRV_NO_IPAY_LIFE = $this->object->srv_no_ipay_life;

// --------------------------------------------------------------------------------------
// URL сайта платежной системы для переадресации клиента на оплату
// --------------------------------------------------------------------------------------

// Тестовый режим работы
$PAY_URL_TEST = $this->object->pay_url_test;
// Тестовый режим: Запрет платежей через тестовый сервер. Необходимо включить после переключения модуля оплаты в реальный режим работы
$TEST_PAY_DENY = $this->object->test_pay_deny;
// Реальный режим работы
$PAY_URL_IPAY_MTS = $this->object->pay_url_ipay_mts;
$PAY_URL_IPAY_LIFE = $this->object->pay_url_ipay_life;
$PAY_URL_WEBMONEY = $this->object->pay_url_webmoney;
$PAY_URL_EASYPAY = $this->object->pay_url_easypay;
$PAY_URL_CARD = $this->object->pay_url_card;


// --------------------------------------------------------------------------------------
// Разрешенные для оплаты платежные системы
// --------------------------------------------------------------------------------------

$PAY_SYSTEM_IPAY_MTS_ENABLED = $this->object->pay_system_ipay_mts_enabled;
$PAY_SYSTEM_IPAY_LIFE_ENABLED = $this->object->pay_system_ipay_life_enabled;
$PAY_SYSTEM_WEBMONEY_ENABLED = $this->object->pay_system_webmoney_enabled;
$PAY_SYSTEM_EASYPAY_ENABLED = $this->object->pay_system_easypay_enabled;
$PAY_SYSTEM_CARD_ENABLED = $this->object->pay_system_card_enabled;

// --------------------------------------------------------------------------------------
// Платежная система
// --------------------------------------------------------------------------------------

// UNKNOWN
$PAY_SYSTEM_UNKNOWN = 'UNKNOWN';
// ERIP
$PAY_SYSTEM_ERIP = 'ERIP';
// iPayMTS
$PAY_SYSTEM_IPAY_MTS = 'iPay-MTS';
// iPayLIFE
$PAY_SYSTEM_IPAY_LIFE = 'iPay-LIFE';

$PAY_SYSTEM_TEST = 'iPayTest';

$pay_system = $PAY_SYSTEM_UNKNOWN;


//define('TEST_MODE', $this->object->test_mode);


// --------------------------------------------------------------------------------------
// Инициализация настроек платежных систем
// --------------------------------------------------------------------------------------
$salt = "";
$srv_no = $SRV_NO_ERIP;
$agent = $_SERVER['HTTP_USER_AGENT'];
switch ($agent) {
    case 'BS_SOU_782':
        // --------------------------------------------------------------------------------------
        // МТС
        // --------------------------------------------------------------------------------------
        $salt = $SALT_IPAY_MTS;
        $pay_system = $PAY_SYSTEM_IPAY_MTS;
        $srv_no = $SRV_NO_IPAY_MTS;
        break;
    case 'BS_SOU_288':
        // --------------------------------------------------------------------------------------
        // life:)
        // --------------------------------------------------------------------------------------
        $salt = $SALT_IPAY_LIFE;
        $pay_system = $PAY_SYSTEM_IPAY_LIFE;
        $srv_no = $SRV_NO_IPAY_LIFE;
        break;
    case 'BeSmart':
        // --------------------------------------------------------------------------------------
        // ЕРИП
        // --------------------------------------------------------------------------------------
        $salt = $SALT_ERIP;
        $pay_system = $PAY_SYSTEM_ERIP;
        $srv_no = $SRV_NO_ERIP;
        break;
    case 'BS_SOU_369':
        // --------------------------------------------------------------------------------------
        // Тестовый сервер
        // --------------------------------------------------------------------------------------
        $salt = $SALT_TEST;
        $pay_system = $PAY_SYSTEM_TEST;
        break;
}
$salt = addslashes($salt);

$SRV_NO = ($TEST_MODE) ? $SRV_NO_TEST : $srv_no;
$DS_SALT = $salt;
$PAYMENT_SYSTEM = $pay_system;
$USER_AGENT = $agent;
$AMOUNT_PRECISION = ($AMT_PRECISION == '') ? 1 : $AMT_PRECISION;

//
$MSG_EMAIL = ($TEST_MODE) ? $TEST_MODE_EMAIL : $SHOP_EMAIL;

$mailsubject = "";
$mailbody = "";


?>
