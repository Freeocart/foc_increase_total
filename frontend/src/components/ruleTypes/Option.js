import React, { useState, useContext } from 'react'
import { ConfigContext } from '../ConfigProvider'
import { useI18n } from 'react-simple-i18n'

export default function Option ({ rulesetId, rule }) {
  const [ selectedOptionId, setSelectedOptionId ] = useState(rule.option_id)
  const [ selectedOptionValueId, setSelectedOptionValueId ] = useState(rule.value)

  const { options, optionsValues, updateRule } = useContext(ConfigContext)

  const { t } = useI18n()

  const handleSelectOptionId = e => {
    setSelectedOptionId(e.target.value)
    updateRule(rulesetId, rule, { option_id: e.target.value, value: null })
  }

  const handleSelectOptionValueId = e => {
    setSelectedOptionValueId(e.target.value)
    updateRule(rulesetId, rule, { value: e.target.value })
  }

  const filteredOptionValues = optionsValues.filter(optionValue => optionValue.option_id === selectedOptionId)

  return <div className="form-horizontal">
    <div className="col-sm-6">
      <select className="form-control" value={ selectedOptionId } onChange={ handleSelectOptionId }>
      { options.map(option => (
        <option value={ option.option_id }>{ option.name }</option>
      ))}
      </select>
    </div>
    <div className="col-sm-6">
      <select className="form-control" value={ selectedOptionValueId } onChange={ handleSelectOptionValueId }>
        <option value={ null }>{ t('Not selected') }</option>
      { filteredOptionValues.map(optionValue => (
        <option value={ optionValue.option_value_id }>{ optionValue.name }</option>
      ))}
      </select>
    </div>
  </div>
}