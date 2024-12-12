<?php

namespace app\Helpers;

use app\Entities\Candidate;
use app\Entities\Application;

class ApplicationHelper
{
    public static function getComparison(Candidate $candidate, Application $vacancy):int
    {
        $sum = 0;
        $all = 2;
        if ($candidate->getExperience() == $vacancy->getExperience()) {
            $sum += 1;
        } elseif ($candidate->getExperience() > $vacancy->getExperience()) {
            $sum += 2;
        }
        foreach ($vacancy->getSkills() as $skill) {
            foreach ($candidate->getSkills() as $candidateSkill) {
                if ($candidateSkill['name'] == $skill['name']) {
                    $sum += 1;
                }
            }
            $all += 1;
        }
        return round(($sum/$all)*100);
    }
}