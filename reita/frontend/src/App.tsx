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

const css: string|null = window.localStorage.getItem("css")

const initDataURL = import.meta.env.VITE_GET_INIT_URL

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
        <link rel="styleseet" href={css} />
        <Header />
        <Routes>
          <Route index element={<Home />} />
          <Route path='/:id' element={<Home />} />
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
