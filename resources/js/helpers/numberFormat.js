const moneyFormat = (value) => {
  if(value && !isNaN(value)) {
    return Number(value).toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
  } else {
    return value
  }
}

module.exports = {
  moneyFormat
}