import { useState, useEffect } from 'react'
import axios from 'axios'

const boardDataURL = import.meta.env.VITE_GET_CONFIG_URL

const Header = () => {

  const [boardData, setBoardData] = useState("")
  useEffect(() => {
    axios.get(boardDataURL).then((response) => {
      setBoardData(response.data);
    });
  }, []);

  return (
    <div>
      <h1>{boardData.boardTitle}</h1>
    </div>
  )
}

export default Header