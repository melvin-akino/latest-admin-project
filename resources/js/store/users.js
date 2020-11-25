import Vue from "vue"
import moment from "moment"

const state = {
  users: [
    {
      id: 1,
      email: "giorodriguez021@gmail.com",
      first_name: "Gio",
      last_name: "Rodriguez",
      credits: 5000,
      currency: "CNY",
      open_bets: 69,
      last_bet: "2020-11-09 08:00:00",
      last_login: "2020-11-09 08:00:00",
      status: "Active",
      created_date: "2020-11-09 08:00:00",
      bets: [
        {
            bet_id: "ML20201116000011",
            post_date: moment().format('YYYY-MM-DD HH:mm:ss'),
            bet_selection: "Barra SC vs Guarani SC<br />\nBarra SC @ 0.76<br />\nFT HDP -0.5(0 - 0)",
            username: "giorodriguez021",
            stake: "50.00",
            price: "0.76",
            towin: "38.00",
            status: "WIN",
            result: "\"2 - 0\"",
            valid_stake: "38.00",
            pl: "38.00",
            remarks: "",
            provider: "HG",
            currency: "CNY"
        },
        {
          bet_id:"ML20201113000048",
          post_date: moment().subtract(1, 'days').format('YYYY-MM-DD HH:mm:ss'),
          bet_selection:"VPK Ahro vs Olimpik <br />\nVPK Ahro@ 1.23<br />\nFT HDP +0.25(1 - 0)",
          username: "giorodriguez021",
          stake: "50.00",
          price: "1.23",
          towin: "61.50",
          status: "HALF WIN",
          result: "\"1 - 0\"",
          valid_stake: "30.75",
          pl: "30.75",
          remarks: "",
          provider: "HG",
          currency: "CNY"
        },
        {
          bet_id:"ML20201113000042",
          post_date: moment().subtract(1, 'week').format('YYYY-MM-DD HH:mm:ss'),
          bet_selection:"Banik Ostrava vs SK Lisen<br />\nSK Lisen @ 0.8<br />\nFT HDP +0.5(0 - 2)",
          username: "giorodriguez021",
          stake: "50.00",
          price: "0.80",
          towin: "40.00",
          status:" WIN",
          result: "\"0 - 2\"",
          valid_stake: "40.00",
          pl: "40.00",
          remarks: "",
          provider: "HG",
          currency: "CNY"
        },
        {
          bet_id:"ML20201113000040",
          post_date: moment().subtract(1, 'month').format('YYYY-MM-DD HH:mm:ss'),
          bet_selection:"Sektzia Nes Tziona vs Hapoel Nof HaGalil<br />\nOver 1.75 @ 0.84 (0 - 0)",
          username: "giorodriguez021",
          stake: "50.00",
          price: "0.84",
          towin: "42.00",
          status: "WIN",
          result: "\"2 - 1\"",
          valid_stake: "42.00",
          pl: "42.00",
          remarks: "",
          provider: "HG",
          currency: "CNY"
        }
      ]
    },
    {
      id: 2,
      email: "kawow@nba.com",
      first_name: "Kawhi",
      last_name: "Leonard",
      credits: 5000000,
      currency: "CNY",
      open_bets: 420,
      last_bet: "2020-11-11 08:00:00",
      last_login: "2020-11-11 08:00:00",
      status: "Active",
      created_date: "2020-11-11 08:00:00",
      bets: [
        {
            bet_id: "ML20201113000020",
            post_date: moment().format('YYYY-MM-DD HH:mm:ss'),
            bet_selection: "Bangladesh vs Nepal<br />\nBangladesh @ 0.63<br />\nFT HDP 0.0(1 - 0)",
            username: "kawow@nba.com",
            stake: "50.00",
            price: "0.63",
            towin: "31.50",
            status: "WIN",
            result: "\"2 - 0\"",
            valid_stake: "31.50",
            pl: "31.50",
            remarks: "",
            provider: "HG",
            currency: "CNY"
        },
        {
          bet_id: "ML20201118000018",
          post_date: moment().subtract(1, 'days').format('YYYY-MM-DD HH:mm:ss'),
          bet_selection: "Perth Glory [Mid] vs Shanghai Greenland Shenhua<br />\nPerth Glory [Mid] @ 1.51<br />\nFT HDP 0.0(0 - 0)",
          username: "kawow@nba.com",
          stake: "50.00",
          price: "1.51",
          towin: "75.50",
          status: "PLACED",
          result: "",
          valid_stake: "0.00",
          pl: "0.00",
          remarks: "",
          provider: "HG",
          currency: "CNY"
        }
      ]
    }
  ],
  userStatus: ["Active", "View Only", "Suspended"],
  currencies: ["CNY", "USD"]
};

const mutations = {
  ADD_USER: (state, payload) => {
    let id = state.users.length + 1
    Vue.set(payload, 'id', id)
    Vue.set(payload, 'bets', [])
    state.users.unshift(payload);
  },
  UPDATE_USER: (state, payload) => {
    let fieldsToUpdate = ["first_name", "last_name", "status", "currency"];
    state.users.map(user => {
      if (user.id == payload.id) {
        fieldsToUpdate.map(field => {
          Vue.set(user, field, payload[field]);
        });
      }
    });
  },
  UPDATE_USER_STATUS: (state, payload) => {
    state.users.map(user => {
      if (user.id == payload.id) {
        Vue.set(user, "status", payload.status);
      }
    });
  },
  UPDATE_USER_WALLET: (state, payload) => {
    state.users.map(user => {
      if (user.id == payload.id) {
        if (payload.wallet.transactionType == "Deposit") {
          Vue.set(
            user,
            "credits",
            Number(user.credits) + Number(payload.wallet.credits)
          );
        } else {
          Vue.set(
            user,
            "credits",
            Number(user.credits) - Number(payload.wallet.credits)
          );
        }
        Vue.set(user, "currency", payload.wallet.currency);
      }
    });
  }
};

export default {
  state,
  mutations,
  namespaced: true
};
