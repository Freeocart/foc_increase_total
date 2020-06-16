import React, { useContext, useState, useEffect } from 'react'
import { ConfigContext } from './ConfigProvider'
import Ruleset from './Ruleset'

import './Rules.css'
import { useI18n } from 'react-simple-i18n'

function Rules ({ outputName }) {
  const { rulesets, addNewRuleset } = useContext(ConfigContext)
  const [ output, setOutput ] = useState("")

  const { t } = useI18n()

  useEffect(() => {
    setOutput(JSON.stringify(rulesets))
  }, [ rulesets ])

  const handleCreateRulesetClick = e => {
    e.preventDefault()
    addNewRuleset()
  }

  return <>
    { Object.entries(rulesets).map(([key, val]) => (
      <div key={ key }>
        <Ruleset id={ key } ruleset={ val } />
      </div>
    )) }

    <textarea className="Rules__output" name={ outputName } value={ output }></textarea>

    <button className="btn btn-success" onClick={ handleCreateRulesetClick }>{ t('Add new ruleset') }</button>
  </>
}

export default Rules