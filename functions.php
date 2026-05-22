<?php
function money($value) {
    return "RM" . number_format((float)$value, 0);
}

function percent($value) {
    return round((float)$value) . "%";
}

function getScoreStatus($score) {
    if ($score >= 75) return "Safe";
    if ($score >= 55) return "Caution";
    return "Risky";
}

function getScoreClass($score) {
    if ($score >= 75) return "safe";
    if ($score >= 55) return "caution";
    return "risky";
}

function calculateMonthlyGoal($goal) {
    $remaining = max(0, (float)$goal['target_amount'] - (float)$goal['current_saved']);
    $months = max(1, (int)$goal['target_months']);
    return $remaining / $months;
}

function calculateHealth($profile, $commitments, $goals) {
    $salary = (float)$profile['monthly_salary'];
    $needs = (float)$profile['fixed_needs'];
    $debt = (float)$profile['existing_debt'];
    $wants = (float)$profile['lifestyle_spending'];
    $savings = (float)$profile['monthly_savings'];
    $emergency = (float)$profile['emergency_fund'];
    $dependents = (int)$profile['dependents'];

    $totalNewCommitment = 0;
    foreach ($commitments as $c) {
        $totalNewCommitment += (float)$c['monthly_amount'];
    }

    $totalGoalSaving = 0;
    foreach ($goals as $g) {
        $totalGoalSaving += calculateMonthlyGoal($g);
    }

    $beforeCommitment = $needs + $debt;
    $afterCommitment = $beforeCommitment + $totalNewCommitment;
    $totalMonthlyPressureAfter = $afterCommitment + $totalGoalSaving;

    $dtiBefore = $salary > 0 ? ($beforeCommitment / $salary) * 100 : 0;
    $dtiAfter = $salary > 0 ? ($afterCommitment / $salary) * 100 : 0;

    $saveBefore = $salary > 0 ? ($savings / $salary) * 100 : 0;
    $effectiveSavingAfter = max(0, $savings - ($totalNewCommitment * 0.25) - $totalGoalSaving);
    $saveAfter = $salary > 0 ? ($effectiveSavingAfter / $salary) * 100 : 0;

    $emergencyBefore = $beforeCommitment > 0 ? $emergency / $beforeCommitment : 0;
    $emergencyAfter = $afterCommitment > 0 ? $emergency / $afterCommitment : 0;

    $remainingAfter = $salary - $needs - $debt - $wants - $savings - $totalNewCommitment - $totalGoalSaving;

    $scoreBefore = 100;
    if ($dtiBefore > 40) $scoreBefore -= 28;
    else if ($dtiBefore > 30) $scoreBefore -= 14;

    if ($saveBefore < 10) $scoreBefore -= 20;
    else if ($saveBefore < 20) $scoreBefore -= 10;

    if ($emergencyBefore < 1) $scoreBefore -= 18;
    else if ($emergencyBefore < 3) $scoreBefore -= 8;

    if ($dependents >= 3 && $emergencyBefore < 4) $scoreBefore -= 8;

    $scoreAfter = 100;
    if ($dtiAfter > 50) $scoreAfter -= 38;
    else if ($dtiAfter > 40) $scoreAfter -= 28;
    else if ($dtiAfter > 30) $scoreAfter -= 14;

    if ($saveAfter < 10) $scoreAfter -= 22;
    else if ($saveAfter < 20) $scoreAfter -= 10;

    if ($emergencyAfter < 1) $scoreAfter -= 20;
    else if ($emergencyAfter < 3) $scoreAfter -= 10;

    if ($remainingAfter < 0) $scoreAfter -= 25;
    if ($totalGoalSaving > $savings) $scoreAfter -= 10;
    if ($dependents >= 3 && $emergencyAfter < 4) $scoreAfter -= 8;

    $scoreBefore = max(8, min(98, round($scoreBefore)));
    $scoreAfter = max(8, min(98, round($scoreAfter)));

    $maxRecommended = max(0, min(($salary * 0.35) - $beforeCommitment, $salary * 0.2, $savings * 1.2));

    return [
        "salary" => $salary,
        "before_commitment" => $beforeCommitment,
        "after_commitment" => $afterCommitment,
        "total_new_commitment" => $totalNewCommitment,
        "total_goal_saving" => $totalGoalSaving,
        "total_monthly_pressure_after" => $totalMonthlyPressureAfter,
        "dti_before" => $dtiBefore,
        "dti_after" => $dtiAfter,
        "save_before" => $saveBefore,
        "save_after" => $saveAfter,
        "emergency_before" => $emergencyBefore,
        "emergency_after" => $emergencyAfter,
        "remaining_after" => $remainingAfter,
        "score_before" => $scoreBefore,
        "score_after" => $scoreAfter,
        "status" => getScoreStatus($scoreAfter),
        "max_recommended" => $maxRecommended
    ];
}

function generateCoachAdvice($health) {
    $advice = [];

    if ($health['dti_after'] > 40) {
        $advice[] = [
            "type" => "warn",
            "title" => "Commitment pressure",
            "text" => "Your commitments are above a safer range. Avoid adding all commitments at once."
        ];
    } else {
        $advice[] = [
            "type" => "good",
            "title" => "Commitment pressure",
            "text" => "Your commitments are still within a manageable range."
        ];
    }

    if ($health['total_goal_saving'] > 0 && $health['save_after'] < 10) {
        $advice[] = [
            "type" => "warn",
            "title" => "Life goal affordability",
            "text" => "Your goals may reduce your monthly savings too much. Extend the timeline or reduce the target amount."
        ];
    } else {
        $advice[] = [
            "type" => "good",
            "title" => "Life goal affordability",
            "text" => "Your life goals are still manageable with your current salary plan."
        ];
    }

    if ($health['emergency_after'] < 3) {
        $advice[] = [
            "type" => "warn",
            "title" => "Emergency buffer",
            "text" => "Your emergency fund covers only " . number_format($health['emergency_after'], 1) . " months after this plan. Aim for at least 3 months."
        ];
    } else {
        $advice[] = [
            "type" => "good",
            "title" => "Emergency buffer",
            "text" => "Your emergency fund can still cover at least 3 months of commitments."
        ];
    }

    if ($health['remaining_after'] < 0) {
        $advice[] = [
            "type" => "bad",
            "title" => "Cash flow warning",
            "text" => "Your monthly cash flow becomes negative. Reduce commitment amount or delay the plan."
        ];
    }

    return $advice;
}
?>
