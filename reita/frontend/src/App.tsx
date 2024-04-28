import { useState, useEffect } from 'react'
import { Route, Routes } from 'react-router-dom'
import axios from 'axios'
import Home from './pages/Home'
import Catalog from './pages/Catalog'
import Reply from './pages/Reply'
import Searches from './pages/Searches'
import './App.css'

const initDataURL = "https://localhost/dev/Reita/reita/backend/getInit.php"
const boardDataURL = "https://localhost/dev/Reita/reita/backend/getConfig.php"

const App = () => {
  const [initData, setInitData] = useState("")
  useEffect(() => {
    axios.get(initDataURL).then((response) => {
      setInitData(response.data);
    });
  }, []);
  const [boardData, setBoardData] = useState("")
  useEffect(() => {
    axios.get(boardDataURL).then((response) => {
      setBoardData(response.data);
    });
  }, []);

  if (initData.flag === false) {
    return (
      <>
        <h1>{boardData.boardTitle}</h1>
        <Routes>
          <Route index element={<Home />} />
          <Route path='catalog' element={<Catalog />} />
          <Route path='Reply' element={<Reply />} />
          <Route path='searches' element={<Searches />} />
          <Route path='*' element={<h2>ページがないよ</h2>} />
        </Routes>
      </>
    )
  } else {
    return (
      <div className='initError'>
        {initData.error}
      </div>
    )
  }

}

export default App
