import { useState, useEffect } from 'react'
import axios from 'axios'

const boardDataURL = "https://localhost/dev/Reita/reita/backend/getConfig.php"

const Header = () => {

  const [boardData, setBoardData] = useState("")
  useEffect(() => {
    axios.get(boardDataURL).then((response) => {
      setBoardData(response.data);
    });
  }, []);

  return (
    <h1>{boardData.boardTitle}</h1>
  )
}

export default Header