import React from 'react'

function GlobalFilterSearch({ filter, setFilter }) {
  return (
    <div className="px-1">
    Search : {' '}
    <input className='border border-gray-300 px-2  bg-gray-50 shadow-lg rounded leading-5 py-1.5 ' type="text" value={filter || ''} onChange={e => setFilter(e.target.value)} />
  </div>
  )
}

export default GlobalFilterSearch