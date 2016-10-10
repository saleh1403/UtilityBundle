<?php

namespace NyroDev\UtilityBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use NyroDev\UtilityBundle\QueryBuilder\AbstractQueryBuilder;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

/**
 * Filter Type for Date range fields.
 */
class FilterRangeNumberType extends FilterType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($builder->has('transformer')) {
            $builder->remove('transformer');
        }
        $builder
            ->add('value', FilterRangeSubType::class, array_merge(array(
                    'type' => NumberType::class,
                    'required' => false,
                ), $options['valueOptions']));
    }

    public function applyFilter(AbstractQueryBuilder $queryBuilder, $name, $data)
    {
        if (isset($data['value']) && $data['value']) {
            $value = array_filter($this->applyValue($data['value']));

            foreach ($value as $k => $val) {
                $queryBuilder->addWhere($name, $k == 'start' ? AbstractQueryBuilder::OPERATOR_GTE : AbstractQueryBuilder::OPERATOR_LTE, $val);
            }
        }

        return $queryBuilder;
    }

    public function getBlockPrefix()
    {
        return 'filter_range_number';
    }

    public function getParent()
    {
        return FilterType::class;
    }
}
