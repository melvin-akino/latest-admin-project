const $axios = require('axios')

const axios = $axios.create({
  baseURL: process.env.MIX_API_URL
})

const walletAxios = $axios.create({
  baseURL: process.env.MIX_WALLET_URL
})

module.exports = {
  axios, walletAxios
}