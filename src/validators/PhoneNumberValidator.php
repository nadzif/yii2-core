<?php

namespace nadzif\core\validators;

use yii\base\InvalidConfigException;
use yii\validators\RegularExpressionValidator;

/**
 * Description of PhoneNumberValidator
 *
 * @author Mohammad Nadzif <demihuman@live.com>
 */
class PhoneNumberValidator extends RegularExpressionValidator
{
    public $pattern = '/^(\+62|62|0)8[11|12|13|14|15|16|17|18|19|21|22|23|28|31|38|51|52|53|55|56|57|58|59|77|78|79|81|82|83|84|87|88|89|95|96|97|98|99|681][0-9]{6,9}$/';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        if ($this->pattern === null) {
            throw new InvalidConfigException('The "pattern" property must be set.');
        }

        if ($this->message === \Yii::t('yii', '{attribute} is invalid.')) {
            $this->message = null;
        }

        if (!$this->message) {
            $this->message = \Yii::t('app', '{attribute} not listed in any registered provider.');
        }
    }
}