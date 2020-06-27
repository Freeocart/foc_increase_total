import React, { useContext } from 'react'
import { ConfigContext } from './ConfigProvider'
import RulePicker from './RulePicker'

import './Ruleset.css'
import RuleCreator from './RuleCreator'
import { useI18n } from 'react-simple-i18n'

export default function Ruleset ({ id, ruleset }) {
  const {
    currencySymbol,
    deleteRuleset,
    updateRuleset
  } = useContext(ConfigContext)

  const { t } = useI18n()

  const handleDeleteRulesetClick = e => {
    e.preventDefault()
    deleteRuleset(id)
  }

  const handleRulesetInfoUpdate = field => e => {
    updateRuleset(id, {
      [field]: e.target.type === 'checkbox' ? e.target.checked : e.target.value
    })
  }

  return <div className="Ruleset">
    <div className="form form-horizontal">
      <div className="row">
        <div className="col-md-6">
          <div className="form-group">
            <label className="col-sm-4 control-label">{ t('Totals label') }</label>
            <div className="col-sm-8">
              <input className="form-control" type="text" value={ ruleset.label } onChange={ handleRulesetInfoUpdate('label') } />
            </div>
          </div>

          <div className="form-group">
            <label className="col-sm-4 control-label">{ t('Extra charge') } (<u>{ currencySymbol }</u>)</label>
            <div className="col-sm-8">
              <input className="form-control" type="text" value={ ruleset.increase } onChange={ handleRulesetInfoUpdate('increase') } />
            </div>
          </div>
        </div>
        <div className="col-md-6">
          <div className="form-group">
            <label className="col-sm-10 control-label">{ t('Use extra charge once') }</label>
            <div className="col-sm-2">
              <div className="checkbox">
                <input className="pull-left" type="checkbox" checked={ ruleset.once } onChange={ handleRulesetInfoUpdate('once') } />
              </div>
            </div>
          </div>

          <div className="form-group">
            <label className="col-sm-10 control-label">{ t("Show extra charge label in cart totals") }</label>
            <div className="col-sm-2">
              <div className="checkbox">
                <input className="pull-left" type="checkbox" checked={ ruleset.useLabel } onChange={ handleRulesetInfoUpdate('useLabel') } />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <h3>{ t('Rules') }</h3>
    <div className="Ruleset__rules_container">
      <div className="Ruleset__rules">
        { ruleset.rules.map((rule, index) => (
          <RulePicker rulesetId={ id } rule={ rule } key={ index } />
        ))}
      </div>
      <RuleCreator rulesetId={ id } />
    </div>

    <hr/>

    <button className="btn btn-danger" onClick={ handleDeleteRulesetClick }>{ t('Delete ruleset') }</button>
  </div>
}