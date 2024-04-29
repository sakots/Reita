import { useState, useEffect } from 'react'
import { Route, Routes } from 'react-router-dom'
import axios from 'axios'
import Home from './pages/Home'
import Catalog from './pages/Catalog'
import Reply from './pages/Reply'
import Searches from './pages/Searches'
import './App.css'
import Header from './components/Header'
import Footer from './components/Footer'

const initDataURL = "https://localhost/dev/Reita/reita/backend/getInit.php"

const App = () => {
  const [initData, setInitData] = useState("")
  useEffect(() => {
    axios.get(initDataURL).then((response) => {
      setInitData(response.data);
    });
  }, []);

  if (initData.flag === false) {
    return (
      <>
        <Header />
        <Routes>
          <Route index element={<Home />} />
          <Route path='catalog' element={<Catalog />} />
          <Route path='Reply' element={<Reply />} />
          <Route path='searches' element={<Searches />} />
          <Route path='*' element={<h2>ページがないよ</h2>} />
        </Routes>
        <Footer />
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
