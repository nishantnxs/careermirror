<?php

namespace App\Enums;

enum AdminPermissionModule: string
{
    case Dashboard = 'dashboard';
    case Employers = 'employers';
    case Candidates = 'candidates';
    case Jobs = 'jobs';
    case Plans = 'plans';
    case Orders = 'orders';
    case Settings = 'settings';
    case Access = 'access';
    case Activity = 'activity';
    case Categories = 'categories';
    case Domains = 'domains';

    public function label(): string
    {
        return match ($this) {
            self::Dashboard => 'Dashboard',
            self::Employers => 'Employers',
            self::Candidates => 'Candidates',
            self::Jobs => 'Jobs',
            self::Plans => 'Plans',
            self::Orders => 'Orders & payments',
            self::Settings => 'Website settings',
            self::Access => 'Roles & moderators',
            self::Activity => 'Activity log',
            self::Categories => 'Categories',
            self::Domains => 'Domains',
        };
    }
}
