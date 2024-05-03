import { useState, useEffect } from 'react'
import Linkify from "linkify-react";
import axios from 'axios'

const boardDataURL = import.meta.env.VITE_GET_CONFIG_URL
const threadDataURL = import.meta.env.VITE_GET_THREAD_URL

const Home = () => {

  const [boardData, setBoardData] = useState("")
  useEffect(() => {
    axios.get(boardDataURL).then((response) => {
      setBoardData(response.data);
    });
  }, []);
  const [threadData, setThreadData] = useState("")
  useEffect(() => {
    axios.get(threadDataURL).then((response) => {
      setThreadData(response.data);
    });
  }, []);

  const linkifyOptions = {
    className: "comment oya"
  }

  const paging = () => {
    const pagingList = threadData.paging ? threadData.paging.map((pageName: string, id: any) =>
      <a href={id + 1} key={id}>[{pageName}]</a>
  ) : null
    return pagingList
  }

  const threads = () => {
    const threadsList = threadData.threads ? threadData.threads.map((threadsName: string, id: number) =>
      <section className="thread" key={id}>
        <h3 className="oyaTitle">[{threadsName.tid}] {threadsName.sub}</h3>
        <section>
        <h4 className="oya">
						<span className="oyaName"><a href={threadsName.a_name}>{threadsName.a_name}</a></span>
						{
              threadsName.admins === 1 ? <svg viewBox="0 0 640 512"><use href="./assets/user-check.svg#admin_badge" /></svg> : null
            }
            {
              threadsName.modified === threadsName.created ? " "+threadsName.modified : threadsName.created
            }
            {
              threadsName.modified === threadsName.created ? null : boardData.updateMark
            }
            {
              threadsName.modified === threadsName.created ? null : threadsName.modified
            }
            {threadsName.mail && <span className="mail"><a href={threadsName.mail}>[mail]</a></span>}
            {threadsName.a_url && <span className="url"><a href={threadsName.a_url} target="_blank" rel="nofollow noopener noreferrer">[URL]</a></span>}
            {boardData.displayId === 1 ? <span className="id"> ID：{threadsName.id} </span> : null}
						<span className="sodane"><a href="">{boardData.favorite}
              {threadsName.exid !== 0 ? null : "x" }
              {threadsName.exid === 0 ? threadsName.exid : " +" }
						</a></span>
					</h4>
          {
            threadsName.picfile &&
            <>
            <h5>
              {threadsName.tool} {threadsName.img_w}x{threadsName.img_h}
              {threadsName.psec !== null || boardData.displayPaintTime !== 1 ? " 描画時間：" : null }
              {threadsName.psec !== null || boardData.displayPaintTime !== 1 ? threadsName.utime : null }
              {threadsName.ext01 === 1 && " ★NSFW"}
            </h5>
            <h5>
              <a target="_blank" href={threadsName.picfile}>{threadsName.picfile}</a>&nbsp;
              {threadsName.tool !== "Chicken Paint" && <a href="" target="_blank">●動画</a>}&nbsp;
              {boardData.useContinue === 1 && <a href="">●続きを描く</a>}
            </h5>
            </>
          }
          {threadsName.ext01 === 1 ? <a className="luminous" href={threadsName.picfile}><span className="nsfw"><img src={threadsName.picfile} alt={threadsName.picfile} loading="lazy" className="image" /></span></a> : <a className="luminous" href={threadsName.picfile}><img src={threadsName.picfile} alt={threadsName.picfile} loading="lazy" className="image" /></a>}
        </section>
        <Linkify as="p" options={linkifyOptions}>{threadsName.com}</Linkify>
      </section>
  ) : null
    return threadsList
  }

  return (
    <>
      <section className="paging">
        <p>
          <span className='se'>{threadData.back === 0 ? "[START]" : <a href={threadData.back}>&lt;&lt;[BACK]</a>}</span>
          {paging()}
          <span className='se'>{threadData.next !== threadData.maxPage ? "[END]" : <a href={threadData.next}>[NEXT]&gt;&gt;</a>}</span>
        </p>
      </section>
      <div>
        {threads()}
      </div>
    </>
  )
}

export default Home