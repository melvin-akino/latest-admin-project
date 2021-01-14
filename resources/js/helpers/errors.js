const handleAPIErrors = (err) => {
  let error = ''
  if(err.response.data.hasOwnProperty('errors')) {
    if(err.response.data.errors.hasOwnProperty('message')) {
      error = err.response.data.errors.message
    } else {
      error = err.response.data.errors[Object.keys(err.response.data.errors)[0]][0]
    }
  } else if(err.response.data.hasOwnProperty('message')) {
    error = err.response.data.message
  } else {
    error = err.response.data.error
  }
  return error
}

module.exports = {
  handleAPIErrors
}