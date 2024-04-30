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

  const paletteList = () => {
    if (boardData.selectPalettes === 1) {
      return (
        <>
          <option value="palette.txt" id="標準">標準</option>
          <option value="PCCS_HSL.txt" id="p_PCCS.txt">PCCS_HSL</option>
          <option value="p_munsellHVC.txt" id="マンセルHV/C">マンセルHV/C</option>
        </>
      )
    } else {
      return <option value="palette.txt" id="標準">標準</option>
    }
  }

  //const addInfoList = () => {
  //  boardData.addInfo.map(infos => (infos.map((info, id) => {return <li key={id}>{info}</li> })))
  //}

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
      <hr />
      <div>
        <section className="ePost">
          <form action="{{$self}}" method="post" encType="multipart/form-data">
            <p>
              <label>幅：<input className="form" type="number" min="300" max={boardData.paintMaxWidth} name="pictureWidth" value={boardData.paintDefaultWidth} required /></label>
              <label>高さ：<input className="form" type="number" min="300" max={boardData.paintMaxHeight}  name="pictureHeight" value={boardData.paintDefaultHeight} required /></label>
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
              {boardData.useAnime === 1 && boardData.defaultAnime === 0 && <label><IgrCheckbox type="checkbox" value="true" name="anime" title="動画記録" defaultChecked={false} />アニメーション記録</label>}
              <input className="button" type="submit" value="お絵かき" />
            </p>
          </form>
          <ul>
            <li>iPadやスマートフォンでも描けるお絵かき掲示板です。</li>
            <li>お絵かきできるサイズは幅300～{boardData.paintMaxWidth}px、高さ300～{boardData.paintMaxHeight}pxです。</li>
          </ul>
        </section>
      </div>
    </div>
  )
}

export default Header