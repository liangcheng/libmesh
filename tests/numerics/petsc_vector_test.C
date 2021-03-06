#include <libmesh/petsc_vector.h>

#ifdef HAVE_PETSC

#include "numeric_vector_test.h"

#include <cppunit/extensions/HelperMacros.h>
#include <cppunit/TestCase.h>

using namespace libMesh;

class PetscVectorTest : public NumericVectorTest<PetscVector<Real> > { 
public: 
  CPPUNIT_TEST_SUITE( PetscVectorTest );

  NUMERICVECTORTEST
  
  CPPUNIT_TEST_SUITE_END();
};

CPPUNIT_TEST_SUITE_REGISTRATION( PetscVectorTest );

#endif // #ifdef HAVE_PETSC

