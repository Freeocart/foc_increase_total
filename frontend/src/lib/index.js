export const intOrNull = value => {
  const number = parseInt(value)
  return isNaN(number) ? null : number
}
