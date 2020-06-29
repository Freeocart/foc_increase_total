import React, { useContext } from 'react'
import { ConfigContext } from './ConfigProvider'
import { useI18n } from 'react-simple-i18n'
import Ruleset from './Ruleset'

import {
  RULES_TOTAL_SET_MAX_INCREASE_VALUE,
  RULES_TOTAL_SET_MIN_INCREASE_VALUE,
  RULES_TOTAL_SUM_INCREASE_VALUES
} from '../constants'

import './Rules.css'

function Rules () {
  const { rulesets, addNewRuleset, totalMode, setTotalMode } = useContext(ConfigContext)

  const { t } = useI18n()

  const handleCreateRulesetClick = e => {
    e.preventDefault()
    addNewRuleset()
  }

  const handleTotalModeChange = e => {
    setTotalMode(e.target.value)
  }

  return <>
    <div className="row">
      <div className="col-md-12">
        <div className="form-group">
          <label className="col-sm-4 control-label">{ t("Total mode") }</label>
          <div className="col-sm-8">
            <select value={ totalMode } onChange={ handleTotalModeChange }>
              <option value={ RULES_TOTAL_SET_MAX_INCREASE_VALUE }>{ t(`Total mode: ${ RULES_TOTAL_SET_MAX_INCREASE_VALUE }`) }</option>
              <option value={ RULES_TOTAL_SET_MIN_INCREASE_VALUE }>{ t(`Total mode: ${ RULES_TOTAL_SET_MIN_INCREASE_VALUE }`) }</option>
              <option value={ RULES_TOTAL_SUM_INCREASE_VALUES }>{ t(`Total mode: ${ RULES_TOTAL_SUM_INCREASE_VALUES }`) }</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    { Object.entries(rulesets).map(([key, val]) => (
      <div key={ key }>
        <Ruleset id={ key } ruleset={ val } />
      </div>
    )) }

    <button className="btn btn-success" onClick={ handleCreateRulesetClick }>{ t('Add new ruleset') }</button>
  </>
}

export default Rules