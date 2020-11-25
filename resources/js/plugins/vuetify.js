import Vue from 'vue'
import Vuetify from 'vuetify'
import i18n from '../i18n'
import '../../sass/_overrides.scss'

Vue.use(Vuetify)

const theme = {
  primary: '#e9954b',
  secondary: '#9C27b0',
  accent: '#9C27b0',
  info: '#00CAE3',
  error: '#D32F2F'
}

export default new Vuetify({
  lang: {
    t: (key, ...params) => i18n.t(key, params),
  },
  theme: {
    themes: {
      dark: theme,
      light: theme,
    },
  },
})
