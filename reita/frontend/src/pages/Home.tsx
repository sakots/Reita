import { useState, useEffect } from 'react'
import Linkify from "linkify-react";
import "linkify-plugin-hashtag";
import axios from 'axios';

const boardDataURL = import.meta.env.VITE_GET_CONFIG_URL
const threadDataURL = import.meta.env.VITE_GET_THREAD_URL

const Home = () => {

  const [theme, setTheme] = useState("./css/reita/mono.min.css")
  const saveTheme = (passedTheme) => {
    window.localStorage.setItem("css", passedTheme)
    setTheme(passedTheme)
  }

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
    className: "comment oya",
    formatHref: {
      hashtag: (href) => "" + href.substr(1),
    },
  }
  const linkifyReplyOptions = {
    className: "comment",
    formatHref: {
      hashtag: (href) => "" + href.substr(1),
    },
  }

  const paging = () => {
    const pagingList = threadData.paging ? threadData.paging.map((pageName: string, id: any) =>
      <a href={id + 1} key={id}>[{pageName}]</a>
  ) : null
    return pagingList
  }

  const threads = () => {
    const threadsList = threadData.threads ? threadData.threads.map((threadsName: string, id: number) =>
      <>
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
        <div className='comment oya'><Linkify as="p" options={linkifyOptions}>{threadsName.com}</Linkify></div>
        {threadsName.reply && threadsName.reply.map((reply: string, id: number) =>
          <div className="res" key={id} >
            <section>
              <h3>[{reply.tid}] {reply.sub}</h3>
              <h4>
								名前：<span className="resname">{reply.a_name}
                {reply.admins === 1 && <svg viewBox="0 0 640 512"><use href="./theme/{{$themedir}}/icons/user-check.svg#admin_badge" /></svg>}
								</span>：
                {reply.modified === reply.created ? reply.modified : reply.created }
                {reply.modified === reply.created ? null : boardData.updateMark}
                {reply.modified === reply.created ? null : reply.modified}
                {reply.mail && <span className="mail"><a href={reply.mail}>[mail]</a></span>}
                {reply.a_url && <span className="url"><a href={reply.a_url} target="_blank" rel="nofollow noopener noreferrer">[URL]</a></span>}
                {boardData.displayId && <span className="id"> ID：{reply.id}</span>}
								<span className="sodane"> <a href="">{boardData.favorite}
                {reply.exid !== 0 ? null : "x" }
                {reply.exid === 0 ? reply.exid : " +" }
								</a></span>
							</h4>
              <div className='comment'><Linkify as={"p"} options={linkifyReplyOptions}>{reply.com}</Linkify></div>
            </section>
          </div>
          )
        }
        <div className="thfoot">
          <span className="button"><a href=""><svg viewBox="0 0 512 512"><use href="../assets/rep.svg#rep" /></svg> 返信</a></span>
          <a href="#header">[↑]</a>
          <hr />
        </div>
      </section>
      </>
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
      <section className="paging">
        <p>
          <span className='se'>{threadData.back === 0 ? "[START]" : <a href={threadData.back}>&lt;&lt;[BACK]</a>}</span>
          {paging()}
          <span className='se'>{threadData.next !== threadData.maxPage ? "[END]" : <a href={threadData.next}>[NEXT]&gt;&gt;</a>}</span>
        </p>
      </section>
      <section>
      <form className="delfo" action="{{$self}}" method="post">
				<p>
          No <input className="form" type="number" min="1" name="delno" defaultValue="" autoComplete="off" required />
          Pass <input className="form" type="password" name="pwd" defaultValue="" autoComplete="current-password" />
          <select className="form" name="mode">
            <option value="edit">編集</option>
            <option value="del">削除</option>
          </select>
          <input className="button" type="submit" value=" OK " />
          </p>
				</form>
				<script>
					colorIdx = GetCookie('_monoreita_colorIdx');
					document.getElementById("mystyle").selectedIndex = colorIdx;
				</script>
			</section>
    </>
  )
}

export default Home