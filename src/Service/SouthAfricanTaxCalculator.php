<?php

namespace Payroll\Service;

class SouthAfricanTaxCalculator
{
    // 2024/2025 Tax Year Rates (March 2024 - February 2025)
    private const PAYE_BRACKETS = [
        ['min' => 0, 'max' => 237100, 'rate' => 0.18, 'base' => 0],
        ['min' => 237101, 'max' => 370500, 'rate' => 0.26, 'base' => 42678],
        ['min' => 370501, 'max' => 512800, 'rate' => 0.31, 'base' => 77362],
        ['min' => 512801, 'max' => 673000, 'rate' => 0.36, 'base' => 121475],
        ['min' => 673001, 'max' => 857900, 'rate' => 0.39, 'base' => 179147],
        ['min' => 857901, 'max' => 1817000, 'rate' => 0.41, 'base' => 251258],
        ['min' => 1817001, 'max' => PHP_INT_MAX, 'rate' => 0.45, 'base' => 644489],
    ];

    // Tax rebates for 2024/2025
    private const PRIMARY_REBATE = 17235;
    private const SECONDARY_REBATE = 9417; // 65 and older
    private const TERTIARY_REBATE = 3141;  // 75 and older

    // UIF Contribution rates (2024/2025)
    private const UIF_RATE_EMPLOYEE = 0.01; // 1%
    private const UIF_RATE_EMPLOYER = 0.01; // 1%
    private const UIF_MAX_MONTHLY = 177.12; // Monthly ceiling (R17,712 annual ceiling / 12)
    private const UIF_MAX_ANNUAL = 21254.40; // Annual ceiling

    // SDL (Skills Development Levy) - 1% of gross payroll, no ceiling
    private const SDL_RATE = 0.01;

    // Medical Aid Tax Credits (2024/2025)
    private const MEDICAL_AID_MAIN_MEMBER = 364;      // per month
    private const MEDICAL_AID_FIRST_DEPENDENT = 364;  // per month
    private const MEDICAL_AID_ADDITIONAL_DEPENDENT = 246; // per month per dependent

    // Retirement Fund Contribution Limits (2024/2025)
    private const RETIREMENT_FUND_LIMIT_ANNUAL = 350000; // Annual limit
    private const RETIREMENT_FUND_LIMIT_MONTHLY = 29166.67; // Monthly limit
    private const RETIREMENT_FUND_MAX_DEDUCTION_RATE = 0.275; // 27.5% of remuneration

    // 2024/2025 PAYE Thresholds
    private const TAX_THRESHOLD_UNDER_65 = 95750;
    private const TAX_THRESHOLD_65_TO_74 = 148217;
    private const TAX_THRESHOLD_75_PLUS = 165689;

    public static function calculatePAYE(float $annualTaxableIncome, int $age = 0): float
    {
        if ($annualTaxableIncome <= 0) {
            return 0.0;
        }

        $tax = 0.0;
        
        foreach (self::PAYE_BRACKETS as $bracket) {
            if ($annualTaxableIncome > $bracket['min']) {
                $taxableInBracket = min($annualTaxableIncome, $bracket['max']) - $bracket['min'] + 1;
                $tax += $taxableInBracket * $bracket['rate'];
            } else {
                break;
            }
        }

        // Apply rebates based on age
        $rebate = self::PRIMARY_REBATE;
        if ($age >= 65 && $age < 75) {
            $rebate += self::SECONDARY_REBATE;
        } elseif ($age >= 75) {
            $rebate += self::SECONDARY_REBATE + self::TERTIARY_REBATE;
        }

        $tax = max(0, $tax - $rebate);

        return round($tax / 12, 2); // Monthly PAYE
    }

    public static function calculateUIF(float $grossMonthlyRemuneration): array
    {
        $monthlyCeiling = self::UIF_MAX_MONTHLY;
        $contributableEarnings = min($grossMonthlyRemuneration, $monthlyCeiling);
        
        $employeeContribution = round($contributableEarnings * self::UIF_RATE_EMPLOYEE, 2);
        $employerContribution = round($contributableEarnings * self::UIF_RATE_EMPLOYER, 2);

        return [
            'employee' => min($employeeContribution, self::UIF_MAX_MONTHLY),
            'employer' => min($employerContribution, self::UIF_MAX_MONTHLY),
            'contributable_earnings' => $contributableEarnings,
        ];
    }

    public static function calculateSDL(float $grossMonthlyPayroll): float
    {
        return round($grossMonthlyPayroll * self::SDL_RATE, 2);
    }

    public static function calculateMedicalAidTaxCredit(int $mainMembers, int $dependents): float
    {
        $credit = 0;
        $credit += $mainMembers * self::MEDICAL_AID_MAIN_MEMBER;
        
        if ($dependents > 0) {
            $credit += self::MEDICAL_AID_FIRST_DEPENDENT;
            if ($dependents > 1) {
                $credit += ($dependents - 1) * self::MEDICAL_AID_ADDITIONAL_DEPENDENT;
            }
        }

        return round($credit, 2);
    }

    public static function calculateRetirementFundDeduction(
        float $grossMonthlyRemuneration,
        float $employeeContribution,
        float $employerContribution = 0
    ): array {
        $annualRemuneration = $grossMonthlyRemuneration * 12;
        $maxDeduction = min(
            $annualRemuneration * self::RETIREMENT_FUND_MAX_DEDUCTION_RATE,
            self::RETIREMENT_FUND_LIMIT_ANNUAL
        );
        $maxMonthlyDeduction = $maxDeduction / 12;

        $totalContribution = $employeeContribution + $employerContribution;
        $allowedDeduction = min($totalContribution, $maxMonthlyDeduction);
        $disallowedContribution = max(0, $totalContribution - $maxMonthlyDeduction);

        return [
            'allowed_deduction' => round($allowedDeduction, 2),
            'disallowed_contribution' => round($disallowedContribution, 2),
            'max_monthly_limit' => round($maxMonthlyDeduction, 2),
            'employee_contribution' => round($employeeContribution, 2),
            'employer_contribution' => round($employerContribution, 2),
        ];
    }

    public static function calculateTaxableIncome(
        float $grossMonthlyIncome,
        float $retirementFundDeduction = 0,
        float $medicalAidContribution = 0,
        float $otherDeductions = 0
    ): float {
        $annualGross = $grossMonthlyIncome * 12;
        $annualDeductions = ($retirementFundDeduction + $medicalAidContribution + $otherDeductions) * 12;
        $annualTaxableIncome = max(0, $annualGross - $annualDeductions);
        
        return round($annualTaxableIncome / 12, 2);
    }

    public static function getTaxThreshold(int $age): int
    {
        if ($age >= 75) {
            return self::TAX_THRESHOLD_75_PLUS;
        } elseif ($age >= 65) {
            return self::TAX_THRESHOLD_65_TO_74;
        }
        return self::TAX_THRESHOLD_UNDER_65;
    }

    public static function calculateAnnualPAYE(float $annualTaxableIncome, int $age = 0): float
    {
        if ($annualTaxableIncome <= 0) {
            return 0.0;
        }

        $tax = 0.0;
        
        foreach (self::PAYE_BRACKETS as $bracket) {
            if ($annualTaxableIncome > $bracket['min']) {
                $taxableInBracket = min($annualTaxableIncome, $bracket['max']) - $bracket['min'] + 1;
                $tax += $taxableInBracket * $bracket['rate'];
            } else {
                break;
            }
        }

        $rebate = self::PRIMARY_REBATE;
        if ($age >= 65 && $age < 75) {
            $rebate += self::SECONDARY_REBATE;
        } elseif ($age >= 75) {
            $rebate += self::SECONDARY_REBATE + self::TERTIARY_REBATE;
        }

        return max(0, $tax - $rebate);
    }

    public static function getUIFCeiling(): float
    {
        return self::UIF_MAX_MONTHLY;
    }

    public static function getSDLRate(): float
    {
        return self::SDL_RATE;
    }

    public static function getRetirementFundLimits(): array
    {
        return [
            'annual_limit' => self::RETIREMENT_FUND_LIMIT_ANNUAL,
            'monthly_limit' => self::RETIREMENT_FUND_LIMIT_MONTHLY,
            'max_deduction_rate' => self::RETIREMENT_FUND_MAX_DEDUCTION_RATE,
        ];
    }

    public static function getMedicalAidCredits(): array
    {
        return [
            'main_member' => self::MEDICAL_AID_MAIN_MEMBER,
            'first_dependent' => self::MEDICAL_AID_FIRST_DEPENDENT,
            'additional_dependent' => self::MEDICAL_AID_ADDITIONAL_DEPENDENT,
        ];
    }

    public static function getTaxThresholds(): array
    {
        return [
            'under_65' => self::TAX_THRESHOLD_UNDER_65,
            '65_to_74' => self::TAX_THRESHOLD_65_TO_74,
            '75_plus' => self::TAX_THRESHOLD_75_PLUS,
        ];
    }
}