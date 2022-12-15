import React, { useState } from 'react'
import LodgeHostelApplication1 from './LodgeHostelApplication1'
import LodgeHostelApplication2 from './LodgeHostelApplication2'
import LodgeHostelApplication3 from './LodgeHostelApplication3'
import LodgeHostelApplicationSubmitted from './LodgeHostelApplicationSubmitted'
import LodgeHostelFeedback from './LodgeHostelFeedback'
import Stepper from './Stepper'

function IndexLodgeHostel() {
  const [screen1Values, setScreen1Values] = useState()

  const [screenLocation, setScreenLocation] = useState(0)


  const screen1Data = (data) => {
    console.log("Screen 1 Data", data)
    setScreenLocation(1)
    setScreen1Values(data)
  }
  const screen2Data = (data) => {
    console.log("Screen 2 Data", data)
    setScreenLocation(2)
  }
  const screen3Data = (data) => {
    console.log("Screen 3 Data", data)
    setScreenLocation(3)
  }



  return (
    <>


      <div className='grid grid-cols-12'>
        <div className='col-span-9'>
          <div className='bg-sky-50 rounded-t-md shadow-lg px-5 py-2 text-lg font-semibold '>Lodge/Hostel Hall Registratin Application</div>
          <div className='bg-white rounded-b-md shadow-lg p-5 border-t'>

            <Stepper />

            <div className={`${screenLocation === 0 ? 'block' : 'hidden'}`}><LodgeHostelApplication1 screen1Data={screen1Data} /></div>
            <div className={`${screenLocation === 1 ? 'block' : 'hidden'}`}><LodgeHostelApplication2 screen2Data={screen2Data} goBack={setScreenLocation} /></div>
            <div className={`${screenLocation === 2 ? 'block' : 'hidden'}`}><LodgeHostelApplication3 screen3Data={screen3Data} goBack={setScreenLocation} /></div>
            <div className={`${screenLocation === 3 ? 'block' : 'hidden'}`}><LodgeHostelApplicationSubmitted /></div>

          </div>
        </div>
        <div className='col-span-3 border ml-2 rounded-md'>
          <div className='bg-sky-50 rounded-t-md shadow-sm border-b px-5 py-3 text-sm font-semibold text-center'>Filled Application Feedback</div>
          <div className='bg-white rounded-b-md shadow-lg p-5 border-t'>
            <LodgeHostelFeedback data={screen1Values} />
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