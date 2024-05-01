import { useState, useEffect } from 'react'
import axios from 'axios'

const boardDataURL = import.meta.env.VITE_GET_CONFIG_URL

const Footer = () => {

  const [boardData, setBoardData] = useState("")
  useEffect(() => {
    axios.get(boardDataURL).then((response) => {
      setBoardData(response.data);
    });
  }, []);

  return (
    <div className="copy">
      <p>
        <a href="https://oekakibbs.moe/" target="_blank">Reita {boardData.ver}</a>
      </p>
      <p>
        OekakiApplet -
        <a href="https://github.com/funige/neo/" target="_blank" rel="noopener noreferrer" title="by funige">PaintBBS NEO</a>
        {boardData.useChicken && ", "}
        {boardData.useChicken && <a href="https://github.com/satopian/ChickenPaint_Be" target="_blank" rel="nofollow noopener noreferrer" title="by Nicholas Sherlock">ChickenPaint Be</a>}
      </p>
      <p>
        UseFunction -
        DynamicPalette{", "}
        <a href="https://huruihone.tumblr.com/" target="_blank" rel="noopener noreferrer" title="by Soto">AppletFit</a>{", "}
        <a href="https://github.com/imgix/luminous" target="_blank" rel="noopener noreferrer" title="by imgix">Luminous</a>{", "}
        <a href="https://github.com/EFTEC/BladeOne" target="_blank" rel="noopener noreferrer" title="by EFTEC">BladeOne</a>
      </p>
    </div>
  )
}

export default Footer