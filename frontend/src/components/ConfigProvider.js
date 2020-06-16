/*
  TODO: refactor file and move it outside of cart directory because its not really component
*/

import React, { useState } from 'react'

const ConfigContext = React.createContext({})

const DEFAULT_RULESET = {
  label: 'ruleset',
  useLabel: false,
  once: true,
  increase: 0,
  rules: []
}

const DEFAULT_OC_INFO = {
  languages: [],
  currencies: [],
  options: [],
  optionsValues: [],
  currencySymbol: ''
}

function ConfigProvider ({ ocInfo, rules:defaultRulesConfig = {}, children }) {
  const [ rulesConfig, setRulesConfig ] = useState(defaultRulesConfig)

  const opencartInfo = Object.assign(DEFAULT_OC_INFO, ocInfo)

  const getRuleset = id => rulesConfig[id]

  const updateRuleset = (id, rulesetData = {}) => {
    const ruleset = getRuleset(id) || {}
    const rules = [
      ...(ruleset.rules || []),
      ...(rulesetData.rules || [])
    ]

    setRulesConfig({
      ...rulesConfig,
      [id]: {
        ...ruleset,
        ...rulesetData,
        rules
      }
    })
  }

  const addNewRuleset = () => {
    const newId = Date.now()
    updateRuleset(newId, DEFAULT_RULESET)
  }

  const addNewRule = (rulesetId, type) => {
    const ruleset = getRuleset(rulesetId)

    updateRuleset(rulesetId, {
      ...ruleset,
      rules: [
        { type }
      ]
    })
  }

  const deleteRuleset = id => {
    const { [id]: _, ...rulesets } = rulesConfig
    setRulesConfig(rulesets)
  }

  const deleteRule = (rulesetId, ruleToDelete) => {
    const ruleset = getRuleset(rulesetId)

    setRulesConfig({
      ...rulesConfig,
      [rulesetId]: {
        ...ruleset,
        rules: ruleset.rules.filter(rule => rule !== ruleToDelete)
      }
    })
  }

  const updateRule = (rulesetId, oldRule, rule) => {
    const ruleset = getRuleset(rulesetId)

    const rules = ruleset.rules.map(r => {
      if (r === oldRule) {
        return {
          ...oldRule,
          ...rule
        }
      }
      return r
    })

    setRulesConfig({
      ...rulesConfig,
      [rulesetId]: {
        ...ruleset,
        rules
      }
    })
  }

  const value = {
    get options () {
      return opencartInfo.options
    },
    get optionsValues () {
      return opencartInfo.optionsValues
    },
    get languages () {
      return opencartInfo.languages
    },
    get currencies () {
      return opencartInfo.currencies
    },
    get currencySymbol () {
      return opencartInfo.currencySymbol
    },
    rulesets: rulesConfig,
    getRuleset,
    updateRuleset,
    addNewRuleset,
    deleteRuleset,
    addNewRule,
    deleteRule,
    updateRule
  }

  return <ConfigContext.Provider value={ value }>{ children }</ConfigContext.Provider>
}

const ConfigConsumer = ConfigContext.Consumer

export { ConfigContext, ConfigProvider, ConfigConsumer }