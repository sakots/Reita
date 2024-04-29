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
      <h1><a href="./">{boardData.boardTitle}</a></h1>
      <div>
        <a href={boardData.home} target="_top">[ホーム]</a>
        <a href="../backend/admin_in.php">[管理モード]</a>
      </div>
      <hr />
      <div>
        <section>
          <p className="top menu">
            <a href="/">[標準モード]</a>
            <a href="/catalog">[カタログ]</a>
            <a href="../backend/picTemp.php">[投稿途中の絵]</a>
            <a href="#footer">[↓]</a>
          </p>
        </section>
      </div>
    </div>
  )
}

export default Header