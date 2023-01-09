<?php

namespace Mautic\LeadBundle\Segment\Decorator\Date\Other;

use Mautic\CoreBundle\Helper\DateTimeHelper;
use Mautic\LeadBundle\Segment\ContactSegmentFilterCrate;
use Mautic\LeadBundle\Segment\Decorator\Date\DateOptionAbstract;
use Mautic\LeadBundle\Segment\Decorator\Date\DateOptionParameters;
use Mautic\LeadBundle\Segment\Decorator\DateDecorator;

class DateRelativeInterval extends DateOptionAbstract
{
    private string $originalValue;

    public function __construct(DateDecorator $dateDecorator, string $originalValue, DateOptionParameters $dateOptionParameters)
    {
        parent::__construct($dateDecorator, $dateOptionParameters);
        $this->originalValue = $originalValue;
    }

    /**
     * @return string
     */
    protected function getModifierForBetweenRange()
    {
        return $this->originalValue;
    }

    /**
     * {@inheritdoc}
     */
    protected function modifyBaseDate(DateTimeHelper $dateTimeHelper)
    {
    }

    /**
     * @return string
     */

    /**
     * @return array|bool|float|string|null
     */
    public function getParameterValue(ContactSegmentFilterCrate $contactSegmentFilterCrate)
    {
        $dateTimeHelper = $this->dateOptionParameters->getDefaultDate();

        $this->modifyBaseDate($dateTimeHelper);

        if ($this->dateOptionParameters->isBetweenRequired()) {
            return $this->getValueForBetweenRange($dateTimeHelper);
        }

        $dateTimeHelper->modify($this->originalValue);

        if (!$this->dateOptionParameters->hasTimePart()) {
            return $dateTimeHelper->getString('Y-m-d');
        }

        return $dateTimeHelper->toUtcString('Y-m-d H:i:s');
    }

    protected function getValueForBetweenRange(DateTimeHelper $dateTimeHelper)
    {
        $dateTimeHelper->modify($this->getModifierForBetweenRange());

        if (!$this->dateOptionParameters->hasTimePart()) {
            return $dateTimeHelper->getString('Y-m-d');
        }

        $dateFormat = 'Y-m-d H:i:s';
        $startWith  = $dateTimeHelper->toUtcString($dateFormat);

        $modifier = '+1 day -1 second';
        $dateTimeHelper->modify($modifier);
        $endWith = $dateTimeHelper->toUtcString($dateFormat);

        return [$startWith, $endWith];
    }

    /**
     * {@inheritdoc}
     */
    protected function getOperatorForBetweenRange(ContactSegmentFilterCrate $leadSegmentFilterCrate): string
    {
        if ($this->dateOptionParameters->hasTimePart()) {
            return '!=' === $leadSegmentFilterCrate->getOperator() ? 'notBetween' : 'between';
        }

        return '!=' === $leadSegmentFilterCrate->getOperator() ? 'notLike' : 'like';
    }
}
