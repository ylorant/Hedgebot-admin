<?php
namespace App\Modules\Twitter\Enum;

class StatusEnum
{
    const DRAFT = "draft";
    const SCHEDULED = "scheduled";
    const SENT = "sent";
    const ERROR = "error";

    public static function getLabels()
    {
        return [
            self::DRAFT => "form.status.draft",
            self::SCHEDULED => "form.status.scheduled",
            self::SENT => "form.status.sent",
            self::ERROR => "form.status.error"
        ];
    }

    public static function getColorClasses()
    {
        return [
            self::DRAFT => "",
            self::SCHEDULED => "col-light-blue",
            self::SENT => "text-success",
            self::ERROR => "text-danger"
        ];
    }

    public static function getBadgeClasses()
    {
        return [
            self::DRAFT => "bg-grey",
            self::SCHEDULED => "bg-indigo",
            self::SENT => "bg-green",
            self::ERROR => "bg-red"
        ];
    }

    public static function getColorClass($status)
    {
        $statusesClasses = self::getColorClasses();

        if(isset($statusesClasses[$status])) {
            return $statusesClasses[$status];
        }

        return null;
    }

    public static function getBadgeClass($status)
    {
        $badgeClasses = self::getBadgeClasses();

        if(isset($badgeClasses[$status])) {
            return $badgeClasses[$status];
        }

        return null;
    }
}