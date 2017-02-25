<?php
/**
 * Created by PhpStorm.
 * User: mchaour
 * Date: 11/02/2017
 * Time: 16:18
 */

namespace Mc\ApiBundle\Utils;

/**
 * List of API status codes.
 *
 * @author Bernard Thomas <bernard.thomas@diapason-info.com>
 */
final class InternalErrorCodes
{
    const SERVER_EXCEPTION          = -1;
    const FORM_EMPTY                = 1;
    const FORM_ERROR                = 2;
    const FORM_EXTRA_FIELD          = 3;
    const PASSWORD_ERROR            = 4;
    const USER_DOESNT_EXIST         = 5;
    const MAIL_NOT_SENT             = 6;
    const DATA_UPDATE_ERROR         = 7;
    const USER_NOT_REGISTERED       = 8;
    const USER_NOT_UPDATED          = 9;
    const TOKEN_NOT_FOUND           = 10;
    const JSON_FILE_NOT_FOUND       = 11;
    const ITEM_NOT_FOUND            = 12;
    const HP_ACCESS_DENIED          = 13;
    const TYPE_NOT_FOUND            = 14;
    const RADIO_NOT_FOUND           = 15;
    const REQUEST_DATA_ERROR        = 16;
    const FAILED_DIGIPLUG_ORDER     = 17;
    const TOKEN_EXPIRED             = 10;
    const USER_ALREADY_PARTICIPATED = 18;
    const COMPETITION_NOT_FOUND     = 19;
    const QUESTION_IS_MANDATORY     = 20;
    const TRACK_ALREADY_EXISTS      = 21;
    const MAX_TRACK_REACHED         = 22;
    const REWARD_NOT_FOUND          = 23;
    const REWARD_EXPIRED            = 24;
    const CONTENT_ACCESS_DENIED     = 25;
    const INVALID_VOUCHER_CODE      = 26;
    const VOUCHER_ALREADY_REDEEMED  = 27;
    
    public static $descriptions = [
        self::REWARD_EXPIRED => 'Offer expired',
        self::INVALID_VOUCHER_CODE => 'Invalid voucher code',
        self::VOUCHER_ALREADY_REDEEMED => 'Voucher already redeemed',
        self::ITEM_NOT_FOUND => 'This Voucher code does not exist'
    ];
}