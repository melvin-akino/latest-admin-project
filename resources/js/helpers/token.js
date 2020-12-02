const Cookies = require('js-cookie')

const getToken = () => {
  return Cookies.get('access_token')
}

module.exports = {
  getToken
}