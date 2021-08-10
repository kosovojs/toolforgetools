import { combineReducers } from 'redux'
import articleReducer from './components/Article/articleSlice'
import appReducer from './components/App/appSlice'

export default combineReducers({
  article: articleReducer,
  app: appReducer
})
