import { getToken } from '../helpers/token'
import router from '../router'
import Cookies from 'js-cookie'

const actions = {
  login({}, loginForm) {
    return new Promise((resolve, reject) => {
      axios.post("login", loginForm)
      .then(response => {
        resolve(response.data)
      })
      .catch(err => {
        reject(err)
      });
    })
  },
  logout({dispatch}) {
    return new Promise((resolve, reject) => {
      axios.post('logout', null, { headers: { 'Authorization': `Bearer ${getToken()}` } })
      .then(response => {
        resolve(response.data.message)
      })
      .catch(err => {
        reject(err)
        dispatch('logoutOnError', err.response.status)
      })
    })
  },
  logoutOnError({}, status) {
    let allowedErrorStatus = [400, 422, 504]
    if(!allowedErrorStatus.includes(status)) {
      Cookies.remove('access_token')
      router.push('/login').catch(err => {})
    }
  }
}

export default {
  actions, namespaced: true
}