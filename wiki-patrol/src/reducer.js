import { combineReducers } from 'redux'
import rcReducer from './components/RecentChanges/slice'
import appReducer from './components/App/appSlice'

export default combineReducers({
  rc: rcReducer,
  app: appReducer
})
