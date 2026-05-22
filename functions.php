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
    $totalMonthlyPressureAfter = $afterCommitment + $wants + $totalGoalSaving;

    $dtiBefore = $salary > 0 ? ($beforeCommitment / $salary) * 100 : 0;
    $dtiAfter = $salary > 0 ? ($afterCommitment / $salary) * 100 : 0;

    $saveBefore = $salary > 0 ? ($savings / $salary) * 100 : 0;
    $effectiveSavingAfter = max(0, $savings - ($totalNewCommitment * 0.20) - $totalGoalSaving);
    $saveAfter = $salary > 0 ? ($effectiveSavingAfter / $salary) * 100 : 0;

    $emergencyBefore = $beforeCommitment > 0 ? $emergency / $beforeCommitment : 0;
    $emergencyAfter = $afterCommitment > 0 ? $emergency / $afterCommitment : 0;

    $remainingAfter = $salary - $needs - $debt - $wants - $savings - $totalNewCommitment - $totalGoalSaving;

    $breakdownBefore = [];
    $breakdownAfter = [];

    $scoreBefore = 100;

    if ($dtiBefore > 40) {
        $scoreBefore -= 28;
        $breakdownBefore[] = ["Commitment ratio", "-28", "Your current fixed commitments are above 40% of salary."];
    } else if ($dtiBefore > 30) {
        $scoreBefore -= 14;
        $breakdownBefore[] = ["Commitment ratio", "-14", "Your current fixed commitments are above the safer 30% range."];
    } else {
        $breakdownBefore[] = ["Commitment ratio", "0", "Your current fixed commitments are still within a safer range."];
    }

    if ($saveBefore < 10) {
        $scoreBefore -= 20;
        $breakdownBefore[] = ["Saving rate", "-20", "Your monthly saving is below 10% of salary."];
    } else if ($saveBefore < 20) {
        $scoreBefore -= 10;
        $breakdownBefore[] = ["Saving rate", "-10", "Your saving rate is acceptable but can be stronger."];
    } else {
        $breakdownBefore[] = ["Saving rate", "0", "Your saving rate is healthy."];
    }

    if ($emergencyBefore < 1) {
        $scoreBefore -= 18;
        $breakdownBefore[] = ["Emergency fund", "-18", "Your emergency fund covers less than 1 month of commitments."];
    } else if ($emergencyBefore < 3) {
        $scoreBefore -= 8;
        $breakdownBefore[] = ["Emergency fund", "-8", "Your emergency fund covers less than 3 months."];
    } else {
        $breakdownBefore[] = ["Emergency fund", "0", "Your emergency fund is in a safer range."];
    }

    if ($dependents >= 3 && $emergencyBefore < 4) {
        $scoreBefore -= 8;
        $breakdownBefore[] = ["Dependents risk", "-8", "More dependents require a stronger emergency buffer."];
    }

    $scoreAfter = 100;

    if ($dtiAfter > 50) {
        $scoreAfter -= 38;
        $breakdownAfter[] = ["Commitment ratio", "-38", "After this plan, commitments exceed 50% of salary."];
    } else if ($dtiAfter > 40) {
        $scoreAfter -= 28;
        $breakdownAfter[] = ["Commitment ratio", "-28", "After this plan, commitments are above 40% of salary."];
    } else if ($dtiAfter > 30) {
        $scoreAfter -= 14;
        $breakdownAfter[] = ["Commitment ratio", "-14", "After this plan, commitments are slightly above the safer range."];
    } else {
        $breakdownAfter[] = ["Commitment ratio", "0", "After this plan, commitments are still within a safer range."];
    }

    if ($saveAfter < 10) {
        $scoreAfter -= 22;
        $breakdownAfter[] = ["Saving rate", "-22", "After this plan, your effective saving rate drops below 10%."];
    } else if ($saveAfter < 20) {
        $scoreAfter -= 10;
        $breakdownAfter[] = ["Saving rate", "-10", "After this plan, your saving rate is moderate but not strong."];
    } else {
        $breakdownAfter[] = ["Saving rate", "0", "After this plan, your saving rate remains healthy."];
    }

    if ($emergencyAfter < 1) {
        $scoreAfter -= 20;
        $breakdownAfter[] = ["Emergency fund", "-20", "After this plan, emergency fund covers less than 1 month."];
    } else if ($emergencyAfter < 3) {
        $scoreAfter -= 10;
        $breakdownAfter[] = ["Emergency fund", "-10", "After this plan, emergency fund covers less than 3 months."];
    } else {
        $breakdownAfter[] = ["Emergency fund", "0", "After this plan, emergency cover is still acceptable."];
    }

    if ($remainingAfter < 0) {
        $scoreAfter -= 25;
        $breakdownAfter[] = ["Cash flow", "-25", "Monthly cash flow becomes negative after this plan."];
    } else if ($remainingAfter < ($salary * 0.05)) {
        $scoreAfter -= 10;
        $breakdownAfter[] = ["Cash flow", "-10", "You have very little monthly buffer left after this plan."];
    } else {
        $breakdownAfter[] = ["Cash flow", "0", "You still have monthly buffer after this plan."];
    }

    if ($totalGoalSaving > $savings) {
        $scoreAfter -= 10;
        $breakdownAfter[] = ["Goal pressure", "-10", "Goal savings require more than your current monthly savings."];
    } else if ($totalGoalSaving > 0) {
        $breakdownAfter[] = ["Goal pressure", "0", "Your goals are still manageable within your savings plan."];
    }

    if ($dependents >= 3 && $emergencyAfter < 4) {
        $scoreAfter -= 8;
        $breakdownAfter[] = ["Dependents risk", "-8", "More dependents require stronger emergency savings."];
    }

    $scoreBefore = max(8, min(98, round($scoreBefore)));
    $scoreAfter = max(8, min(98, round($scoreAfter)));

    $maxRecommended = max(0, min(
        ($salary * 0.35) - $beforeCommitment,
        $salary * 0.20,
        max(0, $remainingAfter + $totalNewCommitment)
    ));

    $status = getScoreStatus($scoreAfter);

    $decisionAction = "Proceed";
    $nextBestAction = "You can proceed, but keep tracking your spending and savings.";

    if ($status === "Caution") {
        $decisionAction = "Reduce or Delay";
        $nextBestAction = "Reduce the new monthly commitment to around " . money($maxRecommended) . " or extend your goal timeline.";
    }

    if ($status === "Risky") {
        $decisionAction = "Avoid for now";
        $nextBestAction = "Do not proceed yet. Reduce commitments, delay the plan, or build at least 3 months of emergency fund first.";
    }

    if ($remainingAfter < 0) {
        $decisionAction = "Avoid for now";
        $nextBestAction = "Your cash flow becomes negative. Reduce the plan by at least " . money(abs($remainingAfter)) . " per month before continuing.";
    }

    return [
        "salary" => $salary,
        "needs" => $needs,
        "debt" => $debt,
        "wants" => $wants,
        "savings" => $savings,
        "emergency" => $emergency,
        "dependents" => $dependents,

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
        "status" => $status,
        "max_recommended" => $maxRecommended,

        "breakdown_before" => $breakdownBefore,
        "breakdown_after" => $breakdownAfter,
        "decision_action" => $decisionAction,
        "next_best_action" => $nextBestAction
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
