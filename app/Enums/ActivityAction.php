<?php

namespace App\Enums;

enum ActivityAction: string
{
    case JobCreated = 'job_created';
    case JobUpdated = 'job_updated';
    case JobDeleted = 'job_deleted';
    case JobExpiryExtended = 'job_expiry_extended';

    case PlanCreated = 'plan_created';
    case PlanUpdated = 'plan_updated';
    case PlanDeleted = 'plan_deleted';
    case PlanStatusChanged = 'plan_status_changed';
    case DiscountApplied = 'discount_applied';

    case EmployerCreated = 'employer_created';
    case EmployerStatusChanged = 'employer_status_changed';
    case EmployerSuspended = 'employer_suspended';
    case EmployerPlanAssigned = 'employer_plan_assigned';
    case EmployerDomainsUpdated = 'employer_domains_updated';

    case CandidateCreated = 'candidate_created';
    case CandidateStatusChanged = 'candidate_status_changed';

    case PaymentUpdated = 'payment_updated';
    case PlanPurchased = 'plan_purchased';

    case SettingsUpdated = 'settings_updated';

    case RoleCreated = 'role_created';
    case RoleUpdated = 'role_updated';
    case RoleDeleted = 'role_deleted';

    case ModeratorCreated = 'moderator_created';
    case ModeratorUpdated = 'moderator_updated';
    case ModeratorStatusChanged = 'moderator_status_changed';
    case ModeratorDeleted = 'moderator_deleted';

    case CategoryCreated = 'category_created';
    case CategoryUpdated = 'category_updated';
    case CategoryDeleted = 'category_deleted';
    case CategorySuggested = 'category_suggested';
    case CategoryApproved = 'category_approved';
    case CategoryRejected = 'category_rejected';

    case DomainCreated = 'domain_created';
    case DomainUpdated = 'domain_updated';
    case DomainDeleted = 'domain_deleted';
    case DomainStatusChanged = 'domain_status_changed';

    public function label(): string
    {
        return match ($this) {
            self::JobCreated => 'Job created',
            self::JobUpdated => 'Job edited',
            self::JobDeleted => 'Job deleted',
            self::JobExpiryExtended => 'Job expiry extended',
            self::PlanCreated => 'Plan created',
            self::PlanUpdated => 'Plan changed',
            self::PlanDeleted => 'Plan deleted',
            self::PlanStatusChanged => 'Plan status changed',
            self::DiscountApplied => 'Discount applied',
            self::EmployerCreated => 'Employer created',
            self::EmployerStatusChanged => 'Employer status changed',
            self::EmployerSuspended => 'Employer suspended',
            self::EmployerPlanAssigned => 'Employer plan assigned',
            self::EmployerDomainsUpdated => 'Employer domains updated',
            self::CandidateCreated => 'Candidate created',
            self::CandidateStatusChanged => 'Candidate status changed',
            self::PaymentUpdated => 'Payment updated',
            self::PlanPurchased => 'Plan purchased',
            self::SettingsUpdated => 'Settings changed',
            self::RoleCreated => 'Role created',
            self::RoleUpdated => 'Role updated',
            self::RoleDeleted => 'Role deleted',
            self::ModeratorCreated => 'Moderator created',
            self::ModeratorUpdated => 'Moderator updated',
            self::ModeratorStatusChanged => 'Moderator status changed',
            self::ModeratorDeleted => 'Moderator deleted',
            self::CategoryCreated => 'Category created',
            self::CategoryUpdated => 'Category updated',
            self::CategoryDeleted => 'Category deleted',
            self::CategorySuggested => 'Category suggested',
            self::CategoryApproved => 'Category approved',
            self::CategoryRejected => 'Category rejected',
            self::DomainCreated => 'Domain created',
            self::DomainUpdated => 'Domain updated',
            self::DomainDeleted => 'Domain deleted',
            self::DomainStatusChanged => 'Domain status changed',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }
}
