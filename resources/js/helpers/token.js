const Cookies = require('js-cookie')

const getToken = () => {
  return Cookies.get('access_token')
}

const getWalletToken = () => {
  return Cookies.get('wallet_token')
}

module.exports = {
  getToken, getWalletToken
}