import Language from './Language'
import Option from './Option'
import Currency from './Currency'

export const RULE_TYPES = {
  language: {
    name: 'Language',
    component: Language
  },
  option: {
    name: 'Option',
    component: Option
  },
  currency: {
    name: 'Currency',
    component: Currency
  }
}

export function getRuleTypeComponent (type) {
  const ruleType = getRuleType(type)
  if (ruleType) {
    return ruleType.component
  }
  return null
}

export function getRuleType (type) {
  if (RULE_TYPES[type]) {
    return RULE_TYPES[type]
  }

  return null
}