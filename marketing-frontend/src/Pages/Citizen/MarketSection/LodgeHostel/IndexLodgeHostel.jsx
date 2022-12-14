import React from 'react'
import LodgeHostelApplication1 from './LodgeHostelApplication1'
import LodgeHostelFeedback from './LodgeHostelFeedback'

function IndexLodgeHostel() {
  return (
    <>



      <div className='grid grid-cols-12'>
        <div className='col-span-9'>
          <div className='bg-sky-50 rounded-t-md shadow-lg px-5 py-2 text-lg font-semibold '>Lodge/Hostel Hall Registratin Application</div>
          <div className='bg-white rounded-b-md shadow-lg p-5 border-t'>

            <LodgeHostelApplication1 />
          </div>
        </div>
        <div className='col-span-3 border ml-2 rounded-md'>
          <div className='bg-sky-50 rounded-t-md shadow-sm border-b px-5 py-3 text-sm font-semibold text-center'>Filled Application Feedback</div>
          <div className='bg-white rounded-b-md shadow-lg p-5 border-t'>
            <LodgeHostelFeedback />
          </div>
        </div>
      </div>

    </>
  )
}

export default IndexLodgeHostel


{/* <div className='bg-sky-50 rounded-t-md shadow-lg px-5 py-2 text-lg font-semibold '>Lodge/Hostel Hall Registratin Application</div>

<div className='bg-white rounded-b-md shadow-lg p-5 border-t'>

  <div className='grid grid-cols-12'>
    <div className='col-span-9'>
      <LodgeHostelApplication1 />
    </div>
    <div className='col-span-3 border ml-2 rounded-md'>
      <div className='bg-sky-50 rounded-t-md shadow-sm border-b px-5 py-2 text-sm font-semibold text-center'>Filled Application Feedback</div>
      <LodgeHostelFeedback />
    </div>
  </div>
</div> */}