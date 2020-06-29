import React, { useState } from 'react'
import { mergeLeft } from '../lib'

import {
  RULES_TOTAL_SET_MAX_INCREASE_VALUE,
  RULES_TOTAL_SET_MIN_INCREASE_VALUE,
  RULES_TOTAL_SUM_INCREASE_VALUES
} from '../constants'

const VALID_TOTAL_MODES = [
  RULES_TOTAL_SET_MAX_INCREASE_VALUE,
  RULES_TOTAL_SET_MIN_INCREASE_VALUE,
  RULES_TOTAL_SUM_INCREASE_VALUES
]

const ConfigContext = React.createContext({})

const DEFAULT_STATE = {
  totalMode: RULES_TOTAL_SET_MAX_INCREASE_VALUE,
  rulesets: {}
}

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
  countries: [],
  attributes: [],
  attributeGroups: [],
  currencySymbol: ''
}

function ConfigProvider ({ ocInfo, state: defaultState = {}, children }) {
  const [ state, setState ] = useState(mergeLeft(DEFAULT_STATE, defaultState))

  const opencartInfo = mergeLeft(DEFAULT_OC_INFO, ocInfo)

  const getRuleset = id => state.rulesets[id]

  const setRulesets = rulesets => {
    setState({
      ...state,
      rulesets
    })
  }

  const updateRuleset = (id, rulesetData = {}) => {
    const ruleset = getRuleset(id) || {}
    const rules = [
      ...(ruleset.rules || []),
      ...(rulesetData.rules || [])
    ]

    setRulesets({
      ...state.rulesets,
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
    const { rulesets: { [id]: _, ...rulesets }, ...rest } = state
    setState({
      ...rest,
      rulesets
    })
  }

  const deleteRule = (rulesetId, ruleToDelete) => {
    const ruleset = getRuleset(rulesetId)

    setRulesets({
      ...state.rulesets,
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

    setRulesets({
      ...state.rulesets,
      [rulesetId]: {
        ...ruleset,
        rules
      }
    })
  }

  const setTotalMode = (totalMode) => {
    if (VALID_TOTAL_MODES.includes(parseInt(totalMode))) {
      setState({
        ...state,
        totalMode
      })
    }
  }

  const value = {
    get state () {
      return state
    },
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
    get countries () {
      return opencartInfo.countries
    },
    get attributes () {
      return opencartInfo.attributes
    },
    get attributeGroups () {
      return opencartInfo.attributeGroups
    },
    get rulesets () {
      return state.rulesets
    },
    resetRulesets() {
      setRulesets({})
    },
    get totalMode () {
      return state.totalMode
    },
    setRulesets,
    setTotalMode,
    getRuleset,
    updateRuleset,
    addNewRuleset,
    deleteRuleset,
    addNewRule,
    deleteRule,
    updateRule,
    resetState (newState) {
      setState(mergeLeft(state, newState))
    }
  }

  return <ConfigContext.Provider value={ value }>{ children }</ConfigContext.Provider>
}

const ConfigConsumer = ConfigContext.Consumer

export { ConfigContext, ConfigProvider, ConfigConsumer }