import { useState, useEffect } from 'react'
import Linkify from 'linkify-react'
import axios from 'axios'

const boardDataURL = import.meta.env.VITE_GET_CONFIG_URL

const Header = () => {

  const [boardData, setBoardData] = useState("")
  useEffect(() => {
    axios.get(boardDataURL).then((response) => {
      setBoardData(response.data);
    });
  }, []);

  const paletteList = () => {
    if (boardData.selectPalettes === 1) {
      const palettes = boardData.palletsData.map((palette: Array, id: number) =>
        <option value={palette[1]} id={palette[0]} key={id}>{palette[0]}</option>
    )
      return palettes

    } else {
      return <option value="palette.txt" id="標準">標準</option>
    }
  }
  const addInfoList = () => {
    const addInfoList = boardData.addInfo ? boardData.addInfo.map((info: string, id: number) =>
      <Linkify as="li" key={id}>{info}</Linkify>
    ) : null
    return addInfoList
  }

  return (
    <div id='header'>
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
      <hr />
      <div>
        <section className="ePost">
          <form action="{{$self}}" method="post" encType="multipart/form-data">
            <p>
              <label>幅：<input className="form" type="number" min="300" max={boardData.paintMaxWidth} name="pictureWidth" defaultValue={boardData.paintDefaultWidth} required /></label>
              <label>高さ：<input className="form" type="number" min="300" max={boardData.paintMaxHeight}  name="pictureHeight" defaultValue={boardData.paintDefaultHeight} required /></label>
              <input type="hidden" name="mode" value="paint" />
              <label htmlFor="tools">ツール</label>
              <select name="tools" id="tools">
                <option value="neo">PaintBBS NEO</option>
                {boardData.useChicken === 1 && <option value="chicken">ChickenPaint</option>}
              </select>
              <label htmlFor="palettes">パレット</label>
              <select name="palettes" id="palettes">
                {paletteList()}
              </select>
              {boardData.useAnime === 1 && boardData.defaultAnime === 1 && <label><input type="checkbox" value="true" name="anime" title="動画記録" defaultChecked />アニメーション記録</label>}
              {boardData.useAnime === 1 && boardData.defaultAnime === 0 && <label><input type="checkbox" value="true" name="anime" title="動画記録" defaultChecked={false} />アニメーション記録</label>}
              <input className="button" type="submit" value="お絵かき" />
            </p>
          </form>
          <ul>
            <li>iPadやスマートフォンでも描けるお絵かき掲示板です。</li>
            <li>お絵かきできるサイズは幅300～{boardData.paintMaxWidth}px、高さ300～{boardData.paintMaxHeight}pxです。</li>
            {addInfoList()}
          </ul>
        </section>
      </div>
    </div>
  )
}

export default Header